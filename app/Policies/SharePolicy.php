<?php

namespace App\Policies;

use App\Models\Share;
use App\Models\User;
use App\Models\GroupUser;
use App\Models\Debt;
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
    public function create(User $user, Debt $debt): bool
    {
        return $user->id === $debt->groupUser->user_id;
    }

    /**
     * Determine whether the user can update the share name
     * Can be done by debt/share owner
     */
    public function updateName(User $user, Share $share): bool
    {
        return $user->id === $share->group_user->user_id || $user->id === $share->debt->groupUser->user_id;
    }

    /**
     * Determine whether the user can update the share amount
     * Can be done by debt owner
     */
    public function updateAmount(User $user, Share $share): bool
    {
        return $user->id === $share->debt->groupUser->user_id;
    }

    /**
     * Determine whether the user can update the share amount
     * Can be done by share owner
     */
    public function updateSent(User $user, Share $share): bool
    {
        return $user->id === $share->group_user->user_id;
    }

    /**
     * Determine whether the user can update the share amount
     * Can be done by debt owner
     */
    public function updateSeen(User $user, Share $share): bool
    {
        return $user->id === $share->debt->groupUser->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Only the debt owner can delete a share.
     */
    public function delete(User $user, Share $share): bool
    {
        return $user->id === $share->debt->groupUser->user_id;
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
