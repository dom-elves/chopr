<?php

namespace App\Observers;

use App\Models\GroupUser;
use App\Notifications\GroupCreatedNotification;

class GroupUserObserver
{
    /**
     * Handle the GroupUser "created" event.
     */
    public function created(GroupUser $groupUser): void
    {
        $groupUser->user->notify(new GroupCreatedNotification($groupUser->group));
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

        // todo:
        // this has now opened a massive can of worms
        // that is prompting me to rethink the db structure
        // may have to change uses of user_id on share, comment and maybe debt
        // to group_user_id
        // either that, or end up with a lot of stupid queries
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
