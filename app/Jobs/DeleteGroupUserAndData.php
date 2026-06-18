<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Debt;
use App\Models\Share;
use Carbon\Carbon;
use App\Jobs\DeleteDebtAndShares;
use App\Jobs\DeleteShare;
use App\Jobs\DeleteGroupUser;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batchable;
use Throwable;
use App\Events\UserBalanceUpdated;
use App\Jobs\DeleteGroupUserAndData;

class DeleteGroupUserAndData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $groupUserId,
        public ?int $newOwnerGroupUserId = null,
    ) {}

    /**
     * The aim of deleting a group user is to also delete their related data,
     * which is debts, shares, comments and aliases. 
     * 
     * Comments & aliases are covered in the GroupUserOberserver, 
     * though debts and shares require extra bits afterwards (ledgers).]
     * 
     * Explaination of building the chain:
     * 
     * 1. Start with an empty $jobs array
     * 2. Query user owned debts, create batch if they exist
     * 3. Same with shares but exclude the ones that would have been included as debt shares
     * 4. Delete the group user
     * 5. Notify (visually update balance) of the user doing the deleting
     * 6. If ownership is being transferred, add that job to the queue
     * 7. Finally, dispatch the chain
    */
    public function handle(): void
    {
        $jobs = [];

        $debtsAndShares = Debt::where('group_user_id', $this->groupUserId)
            ->with('shares.groupUser.user:id')
            ->get();

        if ($debtsAndShares->isNotEmpty()) {
            $deleteDebtJobs = $debtsAndShares->map(
                fn ($debt) => new DeleteDebtAndShares($debt)
            )->all();

            $jobs[] = Bus::batch($deleteDebtJobs)
                ->name('Delete ' . count($deleteDebtJobs) . ' debts for group user ' . $this->groupUserId);
        }

        $shares = Share::where('group_user_id', $this->groupUserId)
            ->whereHas('debt', function ($query) {
                $query->whereColumn('group_user_id', '!=', 'shares.group_user_id');
            })
            ->with([
                'debt.groupUser.user:id',
                'groupUser.user:id'
            ])
            ->get();

        if ($shares->isNotEmpty()) {
            $deleteShareJobs = $shares->map(
                fn ($share) => new DeleteShare($share)
            )->all();

            $jobs[] = Bus::batch($deleteShareJobs)
                ->name('Delete ' . count($deleteShareJobs) . ' shares for group user ' . $this->groupUserId);
        }

        $groupUser = GroupUser::find($this->groupUserId);
        $user = $groupUser->user;

        $jobs[] = new DeleteGroupUser($groupUser);

        $jobs[] = function () use ($user) {
                UserBalanceUpdated::dispatch($user);
            };

        if ($this->newOwnerGroupUserId) {
            $jobs[] = new TransferGroupOwnership($groupUser->group_id, $this->newOwnerGroupUserId);
        }

        Bus::chain($jobs)
            ->catch(function (Throwable $e) {
                dump($e);
            })->dispatch();
    }
}
