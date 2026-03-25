<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use App\Models\Debt;
use Illuminate\Auth\Access\Response;

class CommentPolicy
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
    public function view(User $user, Comment $comment): bool
    {
        return false;
    }

    /**
     * User can comment on a debt if they have a share ($debt->groupUsers relationship) in the debt, 
     * or own the debt itself (no share, but all other shares owe them).
     */
    public function create(User $user, Debt $debt): bool
    {
        return $debt->groupUsers()
            ->where('group_users.user_id', $user->id)
            ->exists() || 
            $debt->groupUser->user_id === $user->id;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $user->id === $comment->groupUser->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Comment $comment): bool
    {
        return $user->id === $comment->groupUser->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Comment $comment): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Comment $comment): bool
    {
        return false;
    }
}
