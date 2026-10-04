<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Cashier);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->hasAnyRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Cashier);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Cashier);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasAnyRole(\App\Enums\UserRole::Admin, \App\Enums\UserRole::Cashier);
    }

    /**
 * A customer is deactivated rather than deleted so their order history
 * and spending totals remain intact. Reactivating is allowed; a customer
 * who still has orders can never be hard-deleted.
 */
    public function delete(User $user, Customer $customer): bool
    {
        return $user->isAdmin() && ! $customer->is_active;
    }
}