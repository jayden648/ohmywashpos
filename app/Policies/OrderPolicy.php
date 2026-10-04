<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Every role can read the order book, though staff never see money.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        return true;
    }

    /**
     * Creating an order happens at the till.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(UserRole::Admin, UserRole::Cashier);
    }

    /**
     * Pricing belongs to admin and cashier.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->hasAnyRole(UserRole::Admin, UserRole::Cashier);
    }

    /**
     * Take payment.
     */
    public function pay(User $user, Order $order): bool
    {
        return $user->hasAnyRole(UserRole::Admin, UserRole::Cashier);
    }

    /**
     * Staff carry out the physical workflow; so may admin and cashier.
     */
    public function advance(User $user, Order $order): bool
    {
        return true;
    }

    /**
     * Quality control is a staff duty, also permitted for supervisors.
     */
    public function performQc(User $user, Order $order): bool
    {
        return true;
    }

    /**
     * Only a supervisor may cancel, and never after pickup.
     */
    public function cancel(User $user, Order $order): bool
    {
        if (! $user->hasAnyRole(UserRole::Admin, UserRole::Cashier)) {
            return false;
        }

        return in_array($order->status, OrderStatus::cancellableFrom(), true);
    }

    /**
     * Deleting an order would destroy financial records.
     */
    public function delete(User $user, Order $order): bool
    {
        return false;
    }
}