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
        return in_array($user->role, [UserRole::Admin, UserRole::Customer, UserRole::Contractor], true);
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($user->role === UserRole::Customer) {
            return (int) $order->customer_id === (int) $user->id;
        }

        if ($user->role === UserRole::Contractor) {
            $supplierId = $user->supplierProfile?->id;

            return $supplierId !== null && (int) $order->supplier_id === (int) $supplierId;
        }

        return false;
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
        return in_array($user->role, [UserRole::Customer, UserRole::Contractor, UserRole::Admin], true);
    }

    public function confirm(User $user, Order $order): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $user->role === UserRole::Customer
            && (int) $order->customer_id === (int) $user->id
            && $order->status === OrderStatus::Pending;
    }

    public function cancel(User $user, Order $order): bool
    {
        if (in_array($order->status, [OrderStatus::Cancelled, OrderStatus::Completed, OrderStatus::Refunded], true)) {
            return false;
        }

        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $user->role === UserRole::Customer
            && (int) $order->customer_id === (int) $user->id
            && in_array($order->status, [OrderStatus::Pending, OrderStatus::Confirmed], true);
    }

    public function transitionStatus(User $user, Order $order): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, Order $order): bool
    {
        return $this->transitionStatus($user, $order);
    }

    public function delete(User $user, Order $order): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $user->role === UserRole::Customer
            && (int) $order->customer_id === (int) $user->id
            && $order->status === OrderStatus::Pending;
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
