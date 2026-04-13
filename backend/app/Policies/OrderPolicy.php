<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $order->customer_id === $user->id;
    }

    public function pay(User $user, Order $order): bool
    {
        if ($order->status !== OrderStatus::Pending) {
            return false;
        }

        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $order->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Customer, UserRole::Contractor, UserRole::Admin]);
    }

    public function update(User $user, Order $order): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $order->customer_id === $user->id;
    }

    public function delete(User $user, Order $order): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $order->customer_id === $user->id && $order->status === OrderStatus::Pending;
    }

    public function restore(User $user, Order $order): bool
    {
        return $this->delete($user, $order);
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return $user->role === UserRole::Admin;
    }
}
