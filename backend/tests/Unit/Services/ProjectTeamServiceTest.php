<?php

namespace Tests\Unit\Services;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use App\Services\ProjectTeamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProjectTeamServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProjectTeamService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ProjectTeamService::class);
    }

    public function test_add_or_invite_adds_member_by_user_id_and_blocks_duplicate(): void
    {
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $engineer = User::factory()->fieldEngineer()->create();

        $result = $this->service->addOrInvite($project, $customer, [
            'user_id' => $engineer->id,
            'project_role' => ProjectRole::Engineer->value,
        ]);

        $this->assertSame('member', $result['type']);
        $this->assertInstanceOf(ProjectMember::class, $result['member']);

        $this->expectException(ValidationException::class);
        $this->service->addOrInvite($project, $customer, [
            'user_id' => $engineer->id,
            'project_role' => ProjectRole::Engineer->value,
        ]);
    }

    public function test_add_or_invite_creates_invitation_for_new_email_and_accepts_it(): void
    {
        $customer = User::factory()->customer()->create(['email' => 'owner@example.test']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $inviteEmail = 'invitee@example.test';

        $inv = $this->service->addOrInvite($project, $customer, [
            'email' => $inviteEmail,
            'project_role' => ProjectRole::Manager->value,
        ]);

        $this->assertSame('invitation', $inv['type']);
        $this->assertNotEmpty($inv['plain_token']);

        $invitee = User::factory()->contractor()->create(['email' => $inviteEmail]);
        $member = $this->service->acceptInvitation($invitee, $inv['plain_token']);
        $this->assertSame($project->id, $member->project_id);
        $this->assertSame($invitee->id, $member->user_id);
    }

    public function test_cannot_invite_owner_role_by_email(): void
    {
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $this->expectException(ValidationException::class);
        $this->service->addOrInvite($project, $customer, [
            'email' => 'x@example.test',
            'project_role' => ProjectRole::Owner->value,
        ]);
    }

    public function test_update_member_role_blocks_changing_primary_owner_role(): void
    {
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $this->expectException(ValidationException::class);
        $this->service->updateMemberRole($project, $customer, ProjectRole::Manager);
    }
}
