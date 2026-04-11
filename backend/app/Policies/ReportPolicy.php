<?php

namespace App\Policies;

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
        // Admin can view any report
        if ($user->role === 'admin') {
            return true;
        }

        // Get the project to check cross-tenant isolation
        $project = $report->project;

        return $project->customer_id === $user->id
            || $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id
            || $report->created_by === $user->id;
    }

    public function create(User $user): bool
    {
        // Only field engineers and supervisors can create reports
        return in_array($user->role, ['field_engineer', 'supervising_architect', 'admin']);
    }

    public function update(User $user, Report $report): bool
    {
        // Admin can update any report
        if ($user->role === 'admin') {
            return true;
        }

        // Only the creator can update
        return $report->created_by === $user->id;
    }

    public function delete(User $user, Report $report): bool
    {
        // Admin can delete any report
        if ($user->role === 'admin') {
            return true;
        }

        // Only the creator or supervising architect can delete
        return $report->created_by === $user->id
            || $report->project->supervising_architect_id === $user->id;
    }

    public function restore(User $user, Report $report): bool
    {
        return $this->delete($user, $report);
    }

    public function forceDelete(User $user, Report $report): bool
    {
        return $user->role === 'admin';
    }
}
