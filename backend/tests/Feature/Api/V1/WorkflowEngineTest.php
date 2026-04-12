<?php

namespace Tests\Feature\Api\V1;

use App\Enums\WorkflowType;
use App\Models\ApprovalRule;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkflowConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_list_workflow_definitions(): void
    {
        $customer = User::factory()->customer()->create();

        $this->actingAs($customer)
            ->getJson('/api/v1/workflows')
            ->assertStatus(403);
    }

    public function test_admin_can_list_workflow_definitions(): void
    {
        $admin = User::factory()->admin()->create();
        WorkflowConfiguration::query()->create([
            'project_id' => null,
            'name' => 'Global',
            'description' => null,
            'type' => WorkflowType::Project,
            'status_transitions' => [],
            'approval_requirements' => [],
            'is_global' => true,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/workflows');

        $response->assertStatus(200)->assertJson(['success' => true]);
        $payload = $response->json('data');
        $items = is_array($payload) && array_key_exists('data', $payload) ? $payload['data'] : $payload;
        $this->assertIsArray($items);
        $this->assertNotEmpty($items);
    }

    public function test_customer_can_start_workflow_and_architect_can_approve(): void
    {
        $customer = User::factory()->customer()->create();
        $architect = User::factory()->supervisingArchitect()->create();
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'supervising_architect_id' => $architect->id,
        ]);

        $config = WorkflowConfiguration::query()->create([
            'project_id' => null,
            'name' => 'Global WF',
            'description' => null,
            'type' => WorkflowType::Project,
            'status_transitions' => [],
            'approval_requirements' => [],
            'is_global' => true,
            'is_active' => true,
        ]);

        ApprovalRule::query()->create([
            'workflow_configuration_id' => $config->id,
            'entity_type' => 'project',
            'status_from' => 'draft',
            'status_to' => 'planning',
            'approver_role' => 'supervising_architect',
            'approval_count' => 1,
        ]);

        $start = $this->actingAs($customer)
            ->postJson("/api/v1/projects/{$project->id}/workflow/start");

        $start->assertStatus(201)->assertJsonPath('data.status', 'in_progress');

        $approvalId = (int) $start->json('data.approvals.0.id');
        $instanceId = (int) $start->json('data.id');

        $pending = $this->actingAs($architect)->getJson('/api/v1/approvals/pending');
        $pending->assertStatus(200)->assertJsonPath('data.0.id', $approvalId);

        $approve = $this->actingAs($architect)
            ->putJson("/api/v1/workflow-instances/{$instanceId}/approve", [
                'approval_id' => $approvalId,
                'notes' => 'موافق',
            ]);

        $approve->assertStatus(200)->assertJsonPath('data.status', 'completed');
    }

    public function test_duplicate_start_returns_422(): void
    {
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $config = WorkflowConfiguration::query()->create([
            'project_id' => null,
            'name' => 'Global WF',
            'description' => null,
            'type' => WorkflowType::Project,
            'status_transitions' => [],
            'approval_requirements' => [],
            'is_global' => true,
            'is_active' => true,
        ]);

        ApprovalRule::query()->create([
            'workflow_configuration_id' => $config->id,
            'entity_type' => 'project',
            'status_from' => 'draft',
            'status_to' => 'planning',
            'approver_role' => 'supervising_architect',
            'approval_count' => 1,
        ]);

        $this->actingAs($customer)
            ->postJson("/api/v1/projects/{$project->id}/workflow/start")
            ->assertStatus(201);

        $this->actingAs($customer)
            ->postJson("/api/v1/projects/{$project->id}/workflow/start")
            ->assertStatus(422);
    }

    public function test_global_workflow_without_project_rules_completes_immediately(): void
    {
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        WorkflowConfiguration::query()->create([
            'project_id' => null,
            'name' => 'Global WF',
            'description' => null,
            'type' => WorkflowType::Project,
            'status_transitions' => [],
            'approval_requirements' => [],
            'is_global' => true,
            'is_active' => true,
        ]);

        $this->actingAs($customer)
            ->postJson("/api/v1/projects/{$project->id}/workflow/start")
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'completed');
    }
}
