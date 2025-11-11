<?php

namespace App\Policies;

use App\Models\GroupUser;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GroupUserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, GroupUser $groupUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, GroupUser $groupUser): bool
    {
        // user can delete group_user if user owns the group
        // this currently doesn't get used anywhere, as the only use is for aliases
        return $user->id === $groupUser->group->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, GroupUser $groupUser): bool
    {
        // user can delete group_user if user owns the group
        return $user->id === $groupUser->group->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, GroupUser $groupUser): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, GroupUser $groupUser): bool
    {
        return false;
    }
}
