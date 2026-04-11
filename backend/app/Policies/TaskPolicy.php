<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        // Admin can view any task
        if ($user->role === 'admin') {
            return true;
        }

        // Get the phase to check cross-tenant isolation
        $phase = $task->phase;
        $project = $phase->project;

        // Project stakeholders and assigned user can view
        return $project->customer_id === $user->id
            || $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id
            || $task->assigned_to === $user->id;
    }

    public function create(User $user): bool
    {
        // Contractors and supervisors can create tasks
        return in_array($user->role, ['contractor', 'supervising_architect', 'admin']);
    }

    public function update(User $user, Task $task): bool
    {
        // Admin can update any task
        if ($user->role === 'admin') {
            return true;
        }

        $phase = $task->phase;
        $project = $phase->project;

        // Project contractor/supervisor and assigned user can update
        return $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id
            || $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        // Admin can delete any task
        if ($user->role === 'admin') {
            return true;
        }

        $phase = $task->phase;
        $project = $phase->project;

        // Only project contractor can delete tasks
        return $project->contractor_id === $user->id;
    }

    public function restore(User $user, Task $task): bool
    {
        return $this->delete($user, $task);
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->role === 'admin';
    }
}
