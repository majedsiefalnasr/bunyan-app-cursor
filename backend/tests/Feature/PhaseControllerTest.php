<?php

namespace Tests\Feature\Api\V1;

use App\Models\Phase;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhaseControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_phases_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);
        Phase::factory()->count(3)->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/projects/{$project->id}/phases");

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(3, 'data');
    }

    public function test_show_phase_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/projects/{$project->id}/phases/{$phase->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $phase->id);
    }

    public function test_create_phase_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/phases", [
                'name' => 'Foundation Phase',
                'budget' => 5000,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Foundation Phase');
    }

    public function test_update_phase_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/v1/projects/{$project->id}/phases/{$phase->id}", [
                'name' => 'Updated Phase Name',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Phase Name');
    }

    public function test_delete_phase_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/projects/{$project->id}/phases/{$phase->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
