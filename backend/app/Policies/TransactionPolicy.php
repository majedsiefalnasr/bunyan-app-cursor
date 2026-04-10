<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function view(User $user, Transaction $transaction): bool
    {
        // Admin can view any transaction
        if ($user->role === 'admin') {
            return true;
        }

        // User can only view their own transactions
        return $transaction->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Only admin can create transactions
        return $user->role === 'admin';
    }

    public function update(User $user, Transaction $transaction): bool
    {
        // Only admin can update transactions
        return $user->role === 'admin';
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        // Only admin can delete transactions
        return $user->role === 'admin';
    }

    public function restore(User $user, Transaction $transaction): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $user->role === 'admin';
    }
}
