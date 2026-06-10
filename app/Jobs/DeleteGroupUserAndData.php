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
use App\Jobs\DeleteGroupUser;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batchable;
use Throwable;
use App\Events\UserBalanceUpdated;

class DeleteGroupUserAndData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $groupUserId
    ) {}

    /**
     * The aim of deleting a group user is to also delete their related data,
     * which is debts, shares, comments and aliases. 
     * 
     * Comments & aliases are covered in the GroupUserOberserver, 
     * though debts and shares require extra bits afterwards (ledgers).
     * 
     * So, to explain:
     * 
     * Query group user w/id
     * Leverage involved() on Debt
     * Map each involved debt to a collection of DeleteDebt jobs
     * Build the chain:
     * 
     * 1. Batch of debt jobs
     * 2. Instantiate new DeleteGroupUser job, fires as it is in chain
     * 3. A callback to fire the UserBalanceUpdated event, which then fires the notif etc
     */
    public function handle(): void
    {
        $groupUser = GroupUser::find($this->groupUserId);
        // todo: fetch this from cache when looking into cache
        $debtsAndShares = Debt::involved($groupUser->user)->with('shares')->get();

        $deleteDebtJobs = $debtsAndShares->map(
            fn ($debt) => new DeleteDebt($debt)
        )->all();

        Bus::chain([
            Bus::batch($deleteDebtJobs)
                ->name('Delete ' . count($deleteDebtJobs) . ' debts for group user ' . $groupUser->id),
            new DeleteGroupUser($groupUser),
            function () use ($groupUser) {
                UserBalanceUpdated::dispatch($groupUser->user);
            }
        ])->catch(function (Throwable $e) {
            // do something here one day
        })->dispatch();
    }
}
