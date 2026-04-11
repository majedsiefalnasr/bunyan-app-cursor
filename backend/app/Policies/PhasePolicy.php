<?php

namespace App\Policies;

use App\Enums\UserRole;
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
        if ($user->role === UserRole::Admin) {
            return true;
        }

        $project = $phase->project;

        return $project->customer_id === $user->id
            || $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Customer, UserRole::Contractor, UserRole::Admin]);
    }

    public function update(User $user, Phase $phase): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        $project = $phase->project;

        return $project->customer_id === $user->id || $project->contractor_id === $user->id;
    }

    public function delete(User $user, Phase $phase): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $phase->project->customer_id === $user->id;
    }

    public function restore(User $user, Phase $phase): bool
    {
        return $this->delete($user, $phase);
    }

    public function forceDelete(User $user, Phase $phase): bool
    {
        return $user->role === UserRole::Admin;
    }
}
