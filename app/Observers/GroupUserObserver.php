<?php

namespace App\Observers;

use App\Models\GroupUser;
use App\Notifications\GroupUserCreatedNotification;

class GroupUserObserver
{
    /**
     * Handle the GroupUser "created" event.
     */
    public function created(GroupUser $groupUser): void
    {
        $groupUser->user->notify(new GroupUserCreatedNotification($groupUser->group));
    }

    /**
     * Handle the GroupUser "updated" event.
     */
    public function updated(GroupUser $groupUser): void
    {
        //
    }

    /**
     * Handle the GroupUser "deleted" event.
     */
    public function deleted(GroupUser $groupUser): void
    {
        foreach ($groupUser->aliases as $alias) {
            $alias->delete();
        }

        foreach ($groupUser->comments as $comment) {
            $comment->delete();
        }

        foreach ($groupUser->shares as $share) {
            $share->delete();
        }
    }

    /**
     * Handle the GroupUser "restored" event.
     */
    public function restored(GroupUser $groupUser): void
    {
        //
    }

    /**
     * Handle the GroupUser "force deleted" event.
     */
    public function forceDeleted(GroupUser $groupUser): void
    {
        //
    }
}
