<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    private function resolveProject(Task $task): Project
    {
        $task->loadMissing('project', 'phase.project');

        return $task->project ?? $task->phase->project;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Task $task): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($task->assigned_to === $user->id) {
            return true;
        }

        $project = $this->resolveProject($task);

        if ($user->role === UserRole::FieldEngineer) {
            return $project->reports()->where('created_by', $user->id)->exists();
        }

        return $project->customer_id === $user->id
            || $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id;
    }

    public function create(User $user, ?Phase $phase = null): bool
    {
        if (! in_array($user->role, [UserRole::Contractor, UserRole::SupervisingArchitect, UserRole::Admin], true)) {
            return false;
        }

        if ($phase === null) {
            return $user->role === UserRole::Admin;
        }

        if ($user->role === UserRole::Admin) {
            return true;
        }

        $project = $phase->project;

        return $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id;
    }

    public function update(User $user, Task $task): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        $project = $this->resolveProject($task);

        return $project->contractor_id === $user->id
            || $project->supervising_architect_id === $user->id
            || $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        $project = $this->resolveProject($task);

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
