<?php

namespace App\Repositories;

use App\Models\ProjectInvitation;
use Illuminate\Database\Eloquent\Collection;

class ProjectInvitationRepository
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): ProjectInvitation
    {
        /** @var ProjectInvitation */
        return ProjectInvitation::query()->create($data);
    }

    /**
     * @return Collection<int, ProjectInvitation>
     */
    public function pendingForProject(int $projectId): Collection
    {
        return ProjectInvitation::query()
            ->where('project_id', $projectId)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->with(['invitedByUser'])
            ->orderByDesc('id')
            ->get();
    }

    public function findPendingByTokenHash(string $tokenHash): ?ProjectInvitation
    {
        return ProjectInvitation::query()
            ->where('token_hash', $tokenHash)
            ->whereNull('accepted_at')
            ->first();
    }

    public function hasPendingForEmail(int $projectId, string $email): bool
    {
        return ProjectInvitation::query()
            ->where('project_id', $projectId)
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->exists();
    }

    public function save(ProjectInvitation $invitation): void
    {
        $invitation->save();
    }
}
