<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\SupplierProfile;
use App\Models\User;

class SupplierProfilePolicy
{
    public function create(User $user): bool
    {
        return $user->role === UserRole::Contractor;
    }

    public function update(User $user, SupplierProfile $supplierProfile): bool
    {
        return $user->role === UserRole::Admin || $supplierProfile->user_id === $user->id;
    }

    public function verify(User $user, SupplierProfile $supplierProfile): bool
    {
        return $user->role === UserRole::Admin;
    }
}
