<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        // Anyone can view active products
        return $product->active || $user->role === 'admin';
    }

    public function create(User $user): bool
    {
        // Only admin can create products
        return $user->role === 'admin';
    }

    public function update(User $user, Product $product): bool
    {
        // Only admin can update products
        return $user->role === 'admin';
    }

    public function delete(User $user, Product $product): bool
    {
        // Only admin can delete products
        return $user->role === 'admin';
    }

    public function restore(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }

    public function forceDelete(User $user, Product $product): bool
    {
        return $user->role === 'admin';
    }
}
