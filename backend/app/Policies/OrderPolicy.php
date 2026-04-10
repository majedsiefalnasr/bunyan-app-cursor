<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Order $order): bool
    {
        // Admin can view any order
        if ($user->role === 'admin') {
            return true;
        }

        // Customer can only view their own orders
        return $order->customer_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Customers and contractors can create orders
        return in_array($user->role, ['customer', 'contractor', 'admin']);
    }

    public function update(User $user, Order $order): bool
    {
        // Admin can update any order
        if ($user->role === 'admin') {
            return true;
        }

        // Customer can only update their own orders
        return $order->customer_id === $user->id;
    }

    public function delete(User $user, Order $order): bool
    {
        // Admin can delete any order
        if ($user->role === 'admin') {
            return true;
        }

        // Customer can only delete their own pending orders
        return $order->customer_id === $user->id && $order->status === 'pending';
    }

    public function restore(User $user, Order $order): bool
    {
        return $this->delete($user, $order);
    }

    public function forceDelete(User $user, Order $order): bool
    {
        return $user->role === 'admin';
    }
}
