<?php

namespace App\Policies;

use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ServiceCategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, ServiceCategory $category): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, ServiceCategory $category): bool
    {
        // A category still holding services must not be removed.
        return $user->isAdmin() && $category->services()->doesntExist();
    }
}