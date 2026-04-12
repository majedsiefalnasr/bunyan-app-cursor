<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Category $category): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $category->is_active;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, Category $category): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->role === UserRole::Admin;
    }
}
