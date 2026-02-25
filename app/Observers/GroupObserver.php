<?php

namespace App\Observers;

use App\Models\Group;
use App\Actions\CreateGroupUser;

class GroupObserver
{
    /**
     * Handle the Group "created" event.
     */
    public function created(Group $group): void
    {
        CreateGroupUser::execute($group->user_id, $group->id);
    }

    /**
     * Handle the Group "updated" event.
     */
    public function updated(Group $group): void
    {
        //
    }

    /**
     * Handle the Group "deleted" event.
     */
    public function deleted(Group $group): void
    {
        foreach ($group->group_users as $group_users) {
            $group_users->delete();
        }

        foreach ($group->debts as $debt) {
            $debt->delete();
        }
    }

    /**
     * Handle the Group "restored" event.
     */
    public function restored(Group $group): void
    {
        //
    }

    /**
     * Handle the Group "force deleted" event.
     */
    public function forceDeleted(Group $group): void
    {
        //
    }
}
