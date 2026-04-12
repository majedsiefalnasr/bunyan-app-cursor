<?php

namespace App\Policies;

use App\Enums\UserRole;
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
        if ($user->role === UserRole::Admin) {
            return true;
        }

        $project = $task->phase->project;

        return $project->customer_id === $user->id
            || $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id
            || $task->assigned_to === $user->id;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Contractor, UserRole::SupervisingArchitect, UserRole::Admin]);
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        $project = $task->phase->project;

        return $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id
            || $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        $project = $task->phase->project;

        if ($user->role === UserRole::SupervisingArchitect) {
            return $project->supervising_architect_id === $user->id;
        }

        return $project->contractor_id === $user->id;
    }

    public function restore(User $user, Task $task): bool
    {
        return $this->delete($user, $task);
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->role === UserRole::Admin;
    }
}
