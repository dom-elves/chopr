<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Group;
use App\Models\GroupUser;


class TransferGroupOwnership implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $groupId,
        public int $newOwnerGroupUserId,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $newGroupUser = GroupUser::findOrFail($this->newOwnerGroupUserId);

        Group::findOrFail($this->groupId)->update([
            'user_id' => $newGroupUser->user->id,
        ]);
    }
}
