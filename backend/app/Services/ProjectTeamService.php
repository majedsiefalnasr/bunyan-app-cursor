<?php

namespace App\Services;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\ProjectMember;
use App\Models\User;
use App\Repositories\ProjectInvitationRepository;
use App\Repositories\ProjectMemberRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ProjectTeamService
{
    public function __construct(
        private ProjectMemberRepository $members,
        private ProjectInvitationRepository $invitations,
    ) {
    }

    /**
     * @return array{members: Collection, invitations_pending: Collection}
     */
    public function listTeam(Project $project): array
    {
        return [
            'members' => $this->members->forProjectWithUsers($project->id),
            'invitations_pending' => $this->invitations->pendingForProject($project->id),
        ];
    }

    /**
     * @param  array{user_id?: int, email?: string, project_role: string}  $data
     * @return array{type: 'member', member: ProjectMember}|array{type: 'invitation', invitation: ProjectInvitation, plain_token: string}
     */
    public function addOrInvite(Project $project, User $actor, array $data): array
    {
        $role = ProjectRole::from($data['project_role']);
        $userId = $data['user_id'] ?? null;

        $this->assertCanAssignRole($project, $role, $userId);

        if ($userId !== null) {
            $user = User::query()->findOrFail($userId);
            if ($this->members->exists($project->id, $user->id)) {
                throw ValidationException::withMessages(['user_id' => ['المستخدم عضو بالفعل في الفريق']]);
            }

            $member = $this->members->create([
                'project_id' => $project->id,
                'user_id' => $user->id,
                'project_role' => $role->value,
                'joined_at' => now(),
            ]);
            $member->load('user');

            return ['type' => 'member', 'member' => $member];
        }

        $email = strtolower((string) $data['email']);

        if ($this->invitations->hasPendingForEmail($project->id, $email)) {
            throw ValidationException::withMessages(['email' => ['توجد دعوة معلقة لهذا البريد بالفعل']]);
        }

        $existing = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();
        if ($existing !== null) {
            if ($this->members->exists($project->id, $existing->id)) {
                throw ValidationException::withMessages(['email' => ['المستخدم عضو بالفعل في الفريق']]);
            }

            $member = $this->members->create([
                'project_id' => $project->id,
                'user_id' => $existing->id,
                'project_role' => $role->value,
                'joined_at' => now(),
            ]);
            $member->load('user');

            return ['type' => 'member', 'member' => $member];
        }

        if ($role === ProjectRole::Owner) {
            throw ValidationException::withMessages(['project_role' => ['لا يمكن دعوة مالك عبر البريد']]);
        }

        $plain = Str::random(48);
        $invitation = $this->invitations->create([
            'project_id' => $project->id,
            'email' => $email,
            'project_role' => $role->value,
            'token_hash' => hash('sha256', $plain),
            'invited_by' => $actor->id,
            'expires_at' => now()->addDays(14),
        ]);
        $invitation->load('invitedByUser');

        return ['type' => 'invitation', 'invitation' => $invitation, 'plain_token' => $plain];
    }

    public function updateMemberRole(Project $project, User $target, ProjectRole $newRole): ProjectMember
    {
        $member = $this->members->findForProjectUserOrFail($project->id, $target->id);

        if ($target->id === $project->customer_id && $member->project_role === ProjectRole::Owner) {
            throw ValidationException::withMessages(['project_role' => ['لا يمكن تغيير دور المالك الأساسي']]);
        }

        $this->assertCanAssignRole($project, $newRole, $target->id);

        $this->members->updateRole($member, $newRole);

        return $member->refresh()->load('user');
    }

    public function removeMember(Project $project, User $target): void
    {
        $member = $this->members->findForProjectUserOrFail($project->id, $target->id);

        if ($target->id === $project->customer_id && $member->project_role === ProjectRole::Owner) {
            throw ValidationException::withMessages(['user_id' => ['لا يمكن إزالة مالك المشروع']]);
        }

        $this->members->delete($member);
    }

    public function acceptInvitation(User $user, string $plainToken): ProjectMember
    {
        $hash = hash('sha256', $plainToken);
        $invitation = $this->invitations->findPendingByTokenHash($hash);

        if ($invitation === null) {
            throw ValidationException::withMessages(['token' => ['الدعوة غير صالحة أو منتهية']]);
        }

        if ($invitation->expires_at->isPast()) {
            throw ValidationException::withMessages(['token' => ['انتهت صلاحية الدعوة']]);
        }

        if (strtolower((string) $user->email) !== strtolower($invitation->email)) {
            throw ValidationException::withMessages(['token' => ['البريد الإلكتروني لا يطابق الدعوة']]);
        }

        $existingMember = $this->members->findForProjectUser($invitation->project_id, $user->id);
        if ($existingMember !== null) {
            $invitation->accepted_at = now();
            $this->invitations->save($invitation);

            return $existingMember->load('user');
        }

        $member = $this->members->create([
            'project_id' => $invitation->project_id,
            'user_id' => $user->id,
            'project_role' => $invitation->project_role->value,
            'joined_at' => now(),
        ]);

        $invitation->accepted_at = now();
        $this->invitations->save($invitation);

        return $member->load('user');
    }

    private function assertCanAssignRole(Project $project, ProjectRole $role, ?int $userId): void
    {
        if ($role !== ProjectRole::Owner) {
            return;
        }

        if ($userId === null || $userId !== $project->customer_id) {
            throw ValidationException::withMessages(['project_role' => ['دور المالك مخصص لحساب العميل للمشروع فقط']]);
        }
    }
}
