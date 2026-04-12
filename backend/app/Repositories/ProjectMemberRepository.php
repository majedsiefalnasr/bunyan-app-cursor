<?php

namespace App\Repositories;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\ProjectMember;
use Illuminate\Database\Eloquent\Collection;

class ProjectMemberRepository
{
    public function ensureOwnerMembership(Project $project): void
    {
        if ($project->customer_id === null) {
            return;
        }

        ProjectMember::query()->firstOrCreate(
            [
                'project_id' => $project->id,
                'user_id' => $project->customer_id,
            ],
            [
                'project_role' => ProjectRole::Owner,
                'joined_at' => now(),
            ],
        );
    }

    /**
     * @return Collection<int, ProjectMember>
     */
    public function forProjectWithUsers(int $projectId): Collection
    {
        return ProjectMember::query()
            ->where('project_id', $projectId)
            ->with(['user'])
            ->orderBy('project_role')
            ->orderBy('id')
            ->get();
    }

    public function exists(int $projectId, int $userId): bool
    {
        return ProjectMember::query()
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ProjectMember
    {
        /** @var ProjectMember */
        return ProjectMember::query()->create($data);
    }

    public function findForProjectUser(int $projectId, int $userId): ?ProjectMember
    {
        return ProjectMember::query()
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findForProjectUserOrFail(int $projectId, int $userId): ProjectMember
    {
        /** @var ProjectMember */
        return ProjectMember::query()
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function updateRole(ProjectMember $member, ProjectRole $role): ProjectMember
    {
        $member->project_role = $role;
        $member->save();

        return $member;
    }

    public function delete(ProjectMember $member): void
    {
        $member->delete();
    }
}
