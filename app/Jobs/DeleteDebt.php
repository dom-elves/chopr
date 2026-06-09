<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Bus\Batchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Debt;
use App\Models\LedgerEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use App\Jobs\DeleteShare;
use App\Jobs\Ledger\DeleteShareLedgerEntry;
use Throwable;
use App\Enums\LedgerEntryType;
use Illuminate\Support\Facades\DB;
use App\Events\UserBalanceUpdated;

class DeleteDebt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Debt $debt,
    ) {}

    /**
     * So to explain the idea here, a certain series of options will happen often:
     * 
     * 1. Debt is deleted
     * 2. Shares must be deleted
     * 3. Ledger entries must be created
     * 4. Balances must be updated
     * 
     * These things have to happen in the order of 3, 2, 1, 4
     * because when a debt is deleted, link to shares is broken etc.
     * So rather than having ->withTrashed() on everything and deleting records
     * in a sort of domino effect, and not making jobs too reliant on one another,
     * A frequent functionality is to bundle it all here (DeleteDebt)
     * 
     * So now to explain how the code actually works:
     * 
     * 1. Map shares to a batch of ledger entry jobs
     * 2. Map shares to delete share jobs
     * 3. Wasn't able to just have $debt->delete() in a batch as it's not a job,
     * so I'll cheat a bit and fire an empty job with a ->then() chained on,
     * which also allowed me to give it a name so it reads nicely in horizon/telescope
     * 
     * These are batches in a chain, because it doesn't *really* matter which order the ledger entries fire in
     * Same with the shares and then the debt, what does matter is that
     * the order is ledgers->shares->debt
     */
    public function handle(): void
    {
        $debt = $this->debt;
        
        DB::transaction(function() use ($debt) {
            foreach ($debt->shares as $share) {
                $debtor = $debt->groupUser->user;

                LedgerEntry::create([
                    'share_id' => $share->id,
                    'user_id' => $debtor->id,
                    'amount' => $share->amount->negated(),
                    'type' => LedgerEntryType::DEBT_OWNERSHIP_DELETED,
                ]);

                DB::table('users')
                    ->where('id', $debtor->id)
                    ->lockForUpdate()
                    ->increment('balance', $share->amount->negated()->getMinorAmount()->toInt());
            
                $indebted = $share->groupUser->user;

                LedgerEntry::create([
                    'share_id' => $share->id,
                    'user_id' => $indebted->id,
                    'amount' => $share->amount,
                    'type' => LedgerEntryType::SHARE_DELETED,
                ]);

                DB::table('users')
                    ->where('id', $indebted->id)
                    ->lockForUpdate()
                    ->increment('balance', $share->amount->getMinorAmount()->toInt());

                // may only be worth showing the logged in user
                // if ($indebted->id !== $debtor->id) {
                //     UserBalanceUpdated::dispatch($indebted);
                // }

                $share->delete();
            };
        }, 5);

        // UserBalanceUpdated::dispatch($debt->groupUser->user);

        $debt->delete();
    }
}
