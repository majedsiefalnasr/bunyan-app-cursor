<?php

namespace Tests\Feature\Api\V1;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_projects_successfully()
    {
        $user = User::factory()->create(['role' => 'customer']);
        $projects = Project::factory()->count(3)->create(['customer_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/projects');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data');
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
            ->assertJsonPath('data.name', 'Test Project');
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
}
