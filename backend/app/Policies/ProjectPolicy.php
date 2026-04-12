<?php

namespace App\Policies;

use App\Enums\UserRole;
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
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($project->members()->where('user_id', $user->id)->exists()) {
            return true;
        }

        if ($user->role === UserRole::FieldEngineer) {
            return $project->reports()->where('created_by', $user->id)->exists();
        }

        return $project->customer_id === $user->id
            || $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Customer || $user->role === UserRole::Admin;
    }

    public function update(User $user, Project $project): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $project->customer_id === $user->id
            || $project->contractor_id === $user->id;
    }

    public function delete(User $user, Project $project): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $project->customer_id === $user->id;
    }

    public function manageTeam(User $user, Project $project): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $project->customer_id === $user->id
            || $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id;
    }

    public function transitionStatus(User $user, Project $project): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if (! in_array($user->role, [UserRole::Customer, UserRole::Contractor, UserRole::SupervisingArchitect], true)) {
            return false;
        }

        return $project->customer_id === $user->id
            || $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id;
    }

    public function approve(User $user, Project $project): bool
    {
        return in_array($user->role, [UserRole::SupervisingArchitect, UserRole::Admin], true)
            && ($project->supervising_architect_id === $user->id || $user->role === UserRole::Admin);
    }

    public function restore(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return $user->role === UserRole::Admin;
    }
}
