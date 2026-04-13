<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\BoqTemplate;
use App\Models\User;

class BoqTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function view(User $user, BoqTemplate $boqTemplate): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, BoqTemplate $boqTemplate): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user, BoqTemplate $boqTemplate): bool
    {
        return $user->role === UserRole::Admin;
    }
}
