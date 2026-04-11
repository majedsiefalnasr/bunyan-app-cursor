<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Report;
use App\Models\User;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Report $report): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        $project = $report->project;

        return $project->customer_id === $user->id
            || $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id
            || $report->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::FieldEngineer, UserRole::SupervisingArchitect, UserRole::Admin]);
    }

    public function update(User $user, Report $report): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $report->created_by === $user->id;
    }

    public function delete(User $user, Report $report): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $report->created_by === $user->id
            || $report->project->supervising_architect_id === $user->id;
    }

    public function restore(User $user, Report $report): bool
    {
        return $this->delete($user, $report);
    }

    public function forceDelete(User $user, Report $report): bool
    {
        return $user->role === UserRole::Admin;
    }
}
