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
use Throwable;
use App\Enums\LedgerEntryType;
use Illuminate\Support\Facades\DB;
use App\Events\UserBalanceUpdated;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class DeleteDebtAndShares implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Debt $debt,
    ) {}

    /**
     * Wrap the entire debt deletion, ledger entries & share deletion in a transaction,
     * doing this makes it sure that either the entire operation completes, or not at all.
     * Retry up to 5 times & use withoutOverlapping middleware to prevent deadlock issues.
     */
    public function handle(): void
    {
        $debtor = $this->debt->groupUser->user;

        DB::transaction(function() use ($debtor) {
            foreach ($this->debt->shares as $share) {
                LedgerEntry::create([
                    'share_id' => $share->id,
                    'user_id' => $debtor->id,
                    'amount' => $share->amount->negated(),
                    'type' => LedgerEntryType::DEBT_OWNERSHIP_DELETED,
                ]);

                DB::table('users')
                    ->where('id', $debtor->id)
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
                    ->increment('balance', $share->amount->getMinorAmount()->toInt());

                $share->delete();
            };

            $this->debt->delete();
        }, 5);
    }

    /**
     * Get the middleware the job should pass through.
     *
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [new WithoutOverlapping($this->debt->id)];
    }
}
