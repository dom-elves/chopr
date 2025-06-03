<?php

namespace App\Observers;

use App\Models\GroupUser;

class GroupUserObserver
{
    /**
     * Handle the GroupUser "created" event.
     */
    public function created(GroupUser $group_user): void
    {
        //
    }

    /**
     * Handle the GroupUser "updated" event.
     */
    public function updated(GroupUser $group_user): void
    {
        if ($group_user->isDirty('balance')) {
            // find the difference between new and old balances
            $old = $group_user->getOriginal('balance');
            $new = $group_user->balance;
            $difference = $new - $old;

            // adjust the total balance
            $user = $group_user->user;
            $user->total_balance += $difference;
            $user->save();
        }
    }

    /**
     * Handle the GroupUser "deleted" event.
     */
    public function deleted(GroupUser $group_user): void
    {
        //
    }

    /**
     * Handle the GroupUser "restored" event.
     */
    public function restored(GroupUser $group_user): void
    {
        //
    }

    /**
     * Handle the GroupUser "force deleted" event.
     */
    public function forceDeleted(GroupUser $group_user): void
    {
        //
    }
}
