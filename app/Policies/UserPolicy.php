<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->is($model);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Only administrators may edit accounts, and never their own
     * (that would let an admin hand their own access to someone else).
     */
    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() && ! $user->is($model);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * The last administrator can never be removed, otherwise the
     * installation would be left with nobody able to manage it.
     */
    public function delete(User $user, User $model): bool
    {
        if (! $user->isAdmin() || $user->is($model)) {
            return false;
        }

        if ($model->role === UserRole::Admin && User::adminCount() <= 1) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    /**
     * Explains why a delete was refused, for use in the UI.
     */
    public function deleteReason(User $user, User $model): ?Response
    {
        if ($this->delete($user, $model)) {
            return Response::allow();
        }

        if ($user->is($model)) {
            return Response::deny('You cannot delete your own account.');
        }

        if ($model->role === UserRole::Admin && User::adminCount() <= 1) {
            return Response::deny('This is the last administrator and cannot be deleted.');
        }

        return Response::deny('You are not allowed to delete users.');
    }
}
