<?php

namespace App\Policies;

use App\Models\Phase;
use App\Models\User;

class PhasePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Phase $phase): bool
    {
        // Admin can view any phase
        if ($user->role === 'admin') {
            return true;
        }

        // Get the project to check cross-tenant isolation
        $project = $phase->project;

        return $project->customer_id === $user->id
            || $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Customers, contractors, and admins can create phases
        return in_array($user->role, ['customer', 'contractor', 'admin']);
    }

    public function update(User $user, Phase $phase): bool
    {
        // Admin can update any phase
        if ($user->role === 'admin') {
            return true;
        }

        $project = $phase->project;

        // Project customer or contractor can update phases
        return $project->customer_id === $user->id || $project->contractor_id === $user->id;
    }

    public function delete(User $user, Phase $phase): bool
    {
        // Admin can delete any phase
        if ($user->role === 'admin') {
            return true;
        }

        $project = $phase->project;

        // Only project customer can delete phases
        return $project->customer_id === $user->id;
    }

    public function restore(User $user, Phase $phase): bool
    {
        return $this->delete($user, $phase);
    }

    public function forceDelete(User $user, Phase $phase): bool
    {
        return $user->role === 'admin';
    }
}
