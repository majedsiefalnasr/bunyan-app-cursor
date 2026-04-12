<?php

namespace Tests\Feature\Api\V1;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTeamControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_list_team_including_owner(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($customer)->getJson("/api/v1/projects/{$project->id}/team");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $members = $response->json('data.members');
        $this->assertIsArray($members);
        $this->assertNotEmpty($members);
        $this->assertSame('owner', $members[0]['project_role']);
    }

    public function test_stranger_cannot_list_team(): void
    {
        $owner = User::factory()->create(['role' => 'customer']);
        $stranger = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $owner->id]);

        $response = $this->actingAs($stranger)->getJson("/api/v1/projects/{$project->id}/team");

        $response->assertStatus(403);
    }

    public function test_contractor_can_add_member_by_user_id(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $contractor = User::factory()->create(['role' => 'contractor']);
        $engineer = User::factory()->create(['role' => 'field_engineer']);

        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
        ]);

        $response = $this->actingAs($contractor)->postJson("/api/v1/projects/{$project->id}/team", [
            'user_id' => $engineer->id,
            'project_role' => 'engineer',
        ]);

        $response->assertStatus(201)->assertJsonPath('data.user.id', $engineer->id);

        $this->assertTrue(
            ProjectMember::query()
                ->where('project_id', $project->id)
                ->where('user_id', $engineer->id)
                ->exists(),
        );
    }

    public function test_customer_cannot_remove_owner(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($customer)->deleteJson("/api/v1/projects/{$project->id}/team/{$customer->id}");

        $response->assertStatus(422);
    }

    public function test_contractor_can_update_member_role(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $contractor = User::factory()->create(['role' => 'contractor']);
        $engineer = User::factory()->create(['role' => 'field_engineer']);

        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
        ]);

        ProjectMember::query()->create([
            'project_id' => $project->id,
            'user_id' => $engineer->id,
            'project_role' => 'engineer',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($contractor)->putJson("/api/v1/projects/{$project->id}/team/{$engineer->id}", [
            'project_role' => 'manager',
        ]);

        $response->assertStatus(200)->assertJsonPath('data.project_role', 'manager');
    }

    public function test_field_engineer_with_report_can_list_team(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $engineer = User::factory()->create(['role' => 'field_engineer']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        Report::factory()->create([
            'project_id' => $project->id,
            'created_by' => $engineer->id,
            'phase_id' => null,
            'task_id' => null,
        ]);

        $response = $this->actingAs($engineer)->getJson("/api/v1/projects/{$project->id}/team");

        $response->assertStatus(200)->assertJsonPath('success', true);
    }

    public function test_field_engineer_cannot_post_team(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $contractor = User::factory()->create(['role' => 'contractor']);
        $engineer = User::factory()->create(['role' => 'field_engineer']);

        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
        ]);

        $response = $this->actingAs($engineer)->postJson("/api/v1/projects/{$project->id}/team", [
            'user_id' => $engineer->id,
            'project_role' => 'viewer',
        ]);

        $response->assertStatus(403);
    }

    public function test_invitation_accept_creates_member(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $inviteEmail = 'invitee-team@example.test';

        $create = $this->actingAs($customer)->postJson("/api/v1/projects/{$project->id}/team", [
            'email' => $inviteEmail,
            'project_role' => 'viewer',
        ]);

        $create->assertStatus(201);
        $token = $create->json('data.accept_token');
        $this->assertIsString($token);

        $invitee = User::factory()->create([
            'role' => 'customer',
            'email' => $inviteEmail,
        ]);

        $accept = $this->actingAs($invitee)->postJson("/api/v1/invitations/{$token}/accept");

        $accept->assertStatus(200)->assertJsonPath('data.project_role', 'viewer');

        $this->assertTrue(
            ProjectMember::query()
                ->where('project_id', $project->id)
                ->where('user_id', $invitee->id)
                ->exists(),
        );
    }

    public function test_invitation_accept_fails_for_wrong_email(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $create = $this->actingAs($customer)->postJson("/api/v1/projects/{$project->id}/team", [
            'email' => 'right@example.test',
            'project_role' => 'worker',
        ]);

        $token = $create->json('data.accept_token');

        $other = User::factory()->create([
            'role' => 'customer',
            'email' => 'wrong@example.test',
        ]);

        $accept = $this->actingAs($other)->postJson("/api/v1/invitations/{$token}/accept");

        $accept->assertStatus(422);
    }
}
