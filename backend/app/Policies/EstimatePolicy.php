<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Estimate;
use App\Models\Project;
use App\Models\User;

class EstimatePolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $user->can('view', $project);
    }

    public function view(User $user, Estimate $estimate): bool
    {
        return $user->can('view', $estimate->project);
    }

    public function compare(User $user, Project $project): bool
    {
        return $user->can('view', $project);
    }

    public function create(User $user, Project $project): bool
    {
        return $user->can('view', $project) && $this->canMutate($user);
    }

    public function update(User $user, Estimate $estimate): bool
    {
        return $user->can('view', $estimate->project) && $this->canMutate($user);
    }

    public function calculate(User $user, Estimate $estimate): bool
    {
        return $this->update($user, $estimate);
    }

    public function manageItems(User $user, Estimate $estimate): bool
    {
        return $this->update($user, $estimate);
    }

    public function approve(User $user, Estimate $estimate): bool
    {
        if (! $user->can('view', $estimate->project)) {
            return false;
        }

        return in_array($user->role, [
            UserRole::Customer,
            UserRole::SupervisingArchitect,
            UserRole::Admin,
        ], true);
    }

    public function reject(User $user, Estimate $estimate): bool
    {
        return $this->approve($user, $estimate);
    }

    public function export(User $user, Estimate $estimate): bool
    {
        return $this->view($user, $estimate);
    }

    private function canMutate(User $user): bool
    {
        return in_array($user->role, [
            UserRole::Customer,
            UserRole::Contractor,
            UserRole::SupervisingArchitect,
            UserRole::Admin,
        ], true);
    }
}
