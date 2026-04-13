<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Customer, UserRole::Admin], true);
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return (int) $payment->user_id === (int) $user->id;
    }
}
