<?php

namespace App\Policies;

use App\Models\Debt;
use App\Models\User;
use App\Models\Group;
use Illuminate\Auth\Access\Response;

class DebtPolicy
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
    public function view(User $user, Debt $debt): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     * All debt creation comes with a group id
     * this is taken from the request in the store() controller method
     */
    public function create(User $user, Group $group): bool
    {
        return $group->users()
            ->where('users.id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Debt $debt): bool
    {
        return $user->id === $debt->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Debt $debt): bool
    {
        return $user->id === $debt->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Debt $debt): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Debt $debt): bool
    {
        return false;
    }
}
