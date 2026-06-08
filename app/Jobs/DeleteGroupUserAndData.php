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
use Throwable;
use Illuminate\Support\Facades\Log;

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
     * Deleting a group user will always delete their debts & related data,
     * most of that functionality is in the DeleteDebt job, but this can be a 
     * higher link on the chain, but DeleteDebt is the latest possible start of the chain.
     */
    public function handle(): void
    {
        $groupUser = GroupUser::find($this->groupUserId);
        // todo: fetch this from cache when looking into cache
        $debtsAndShares = Debt::involved($groupUser->user)->with('shares')->get();

        $debts = $debtsAndShares->map(
            fn ($debt) => new DeleteDebt($debt)
        )->all();

        Log::info('Start group user ' . $groupUser->id . ' deletion ' . Carbon::now());

        Bus::chain([
            Bus::batch($debts)
                ->name('Delete ' . count($debts) . ' debts for group user ' . $groupUser->id),
            Bus::batch([new DeleteGroupUser($groupUser)])
                ->name('Delete group user ' . $groupUser->id)
        ])->catch(function (Throwable $e) {
            // do something here one day
        })->dispatch();

        Log::info('Finished group user ' . $groupUser->id . ' deletion ' . Carbon::now());
    }
}
