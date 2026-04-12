<?php

namespace Tests\Feature\Api\V1;

use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_projects_successfully()
    {
        $user = User::factory()->create(['role' => 'customer']);
        Project::factory()->count(3)->create(['customer_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/projects');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $payload = $response->json('data');
        $items = is_array($payload) && array_key_exists('data', $payload) ? $payload['data'] : $payload;
        $this->assertIsArray($items);
        $this->assertCount(3, $items);
    }

    public function test_show_project_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.id', $project->id);
    }

    public function test_show_project_forbidden_for_other_user()
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $owner->id]);

        $response = $this->actingAs($stranger)
            ->getJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(403);
    }

    public function test_field_engineer_can_view_project_with_report()
    {
        $engineer = User::factory()->create(['role' => 'field_engineer']);
        $project = Project::factory()->create();
        Report::factory()->create([
            'project_id' => $project->id,
            'created_by' => $engineer->id,
            'phase_id' => null,
            'task_id' => null,
        ]);

        $response = $this->actingAs($engineer)
            ->getJson("/api/v1/projects/{$project->id}");

        $response->assertStatus(200)->assertJsonPath('data.id', $project->id);
    }

    public function test_create_project_successfully()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/projects', [
                'name' => 'Test Project',
                'description' => 'A test project',
                'budget' => 10000,
                'location' => 'Riyadh',
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name', 'Test Project')
            ->assertJsonPath('data.status', 'draft');
    }

    public function test_create_project_with_invalid_budget()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/projects', [
                'name' => 'Test Project',
                'budget' => 'invalid',
                'location' => 'Riyadh',
            ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_update_project_successfully()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $user = User::factory()->create(['role' => 'contractor']);
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/v1/projects/{$project->id}", [
                'name' => 'Updated Project Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Project Name');
    }

    public function test_transition_status_successfully()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($customer)
            ->putJson("/api/v1/projects/{$project->id}/status", [
                'status' => 'planning',
            ]);

        $response->assertStatus(200)->assertJsonPath('data.status', 'planning');
    }

    public function test_transition_status_rejects_invalid_graph()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($customer)
            ->putJson("/api/v1/projects/{$project->id}/status", [
                'status' => 'closed',
            ]);

        $response->assertStatus(422);
    }

    public function test_timeline_returns_structure()
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $project = Project::factory()->create(['customer_id' => $customer->id]);

        $response = $this->actingAs($customer)
            ->getJson("/api/v1/projects/{$project->id}/timeline");

        $response->assertStatus(200)
            ->assertJsonPath('data.project.id', $project->id)
            ->assertJsonStructure(['data' => ['project', 'phases']]);
    }
}
