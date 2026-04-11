<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $transaction->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function restore(User $user, Transaction $transaction): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $user->role === UserRole::Admin;
    }
}
