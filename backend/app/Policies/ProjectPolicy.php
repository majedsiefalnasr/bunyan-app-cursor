<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Project $project): bool
    {
        // Admin can view any project
        if ($user->role === 'admin') {
            return true;
        }

        // Cross-tenant isolation: User can only view their projects
        return $project->customer_id === $user->id
            || $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id;
    }

    public function create(User $user): bool
    {
        // Only customers can create projects
        return $user->role === 'customer' || $user->role === 'admin';
    }

    public function update(User $user, Project $project): bool
    {
        // Admin can update any project
        if ($user->role === 'admin') {
            return true;
        }

        // Only the customer who owns the project can update it
        return $project->customer_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        // Admin can delete any project
        if ($user->role === 'admin') {
            return true;
        }

        // Only the customer owner can delete
        return $project->customer_id === $user->id;
    }

    public function approve(User $user, Project $project): bool
    {
        // Only supervising architects and admins can approve projects
        return in_array($user->role, ['supervising_architect', 'admin'])
            && (in_array($user->id, [$project->supervising_architect_id]) || $user->role === 'admin');
    }

    public function restore(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return $user->role === 'admin';
    }
}
