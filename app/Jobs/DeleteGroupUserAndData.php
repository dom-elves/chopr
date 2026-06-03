<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\GroupUser;
use App\Models\Debt;
use Carbon\Carbon;
use App\Jobs\DeleteDebt;
use App\Jobs\DeleteShare;
use App\Jobs\Ledger\DeleteShareLedgerEntry;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batchable;

class DeleteGroupUserAndData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $groupUserId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $groupUser = GroupUser::find($this->groupUserId);
        // query data
        $debtsAndShares = Debt::involved($groupUser->user)->with('shares')->get();
        // separate into debt->shares->ledger chains
        $ledgerEntries = $debtsAndShares->flatMap(
            fn ($debt) => $debt->shares->map(
                fn ($share) => new DeleteShareLedgerEntry($share)                
            )
        )->all();

        $shares = $debtsAndShares->flatMap(
            fn ($debt) => $debt->shares->map(
                fn ($share) => new DeleteShare($share)                
            )
        )->all();

        $debts = $debtsAndShares->map(
            fn ($debt) => new DeleteDebt($debt)
        )->all();

        $jobs = array_merge($ledgerEntries, $shares, $debts, [new DeleteGroupUser($groupUser)]);

        dump($jobs);

        // batch the chains
        Bus::chain([
            Bus::batch($ledgerEntries)
                ->name('Delete ' . count($ledgerEntries) . ' ledger entries for group user ' . $groupUser->id),
            Bus::batch($shares)
                ->name('Delete ' . count($shares) . ' shares for group user ' . $groupUser->id),
            Bus::batch($debts)
                ->name('Delete ' . count($debts) . ' debts for group user ' . $groupUser->id),
            Bus::batch([new DeleteGroupUser($groupUser)])
                ->name('Delete group user ' . $groupUser->id)
        ])->catch(function (Throwable $e) {
            // do something here one day
        })->dispatch();

    }
}
