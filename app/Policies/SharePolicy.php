<?php

namespace App\Policies;

use App\Models\Share;
use App\Models\User;
use App\Models\GroupUser;
use Illuminate\Auth\Access\Response;

class SharePolicy
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
    public function view(User $user, Share $share): bool
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
    public function update(User $user, Share $share): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Share $share): bool
    {
        $group_user_ids = GroupUser::where('user_id', $user->id)->get()->pluck('id')->toArray();

        if (!in_array($share->group_user_id, $group_user_ids)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Share $share): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Share $share): bool
    {
        return false;
    }
}
