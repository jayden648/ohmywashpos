<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(UserRole::Admin, UserRole::Cashier);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->hasAnyRole(UserRole::Admin, UserRole::Cashier);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(UserRole::Admin, UserRole::Cashier);
    }

    /**
     * Settled payments are financial records and are never edited.
     */
    public function update(User $user, Payment $payment): bool
    {
        return false;
    }

    public function delete(User $user, Payment $payment): bool
    {
        return false;
    }
}