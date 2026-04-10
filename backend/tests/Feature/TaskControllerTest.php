<?php

namespace Tests\Feature\Api\V1;

use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_tasks_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);
        Task::factory()->count(3)->create(['phase_id' => $phase->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/projects/{$project->id}/phases/{$phase->id}/tasks");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_show_task_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create(['phase_id' => $phase->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/projects/{$project->id}/phases/{$phase->id}/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $task->id);
    }

    public function test_create_task_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($user)
            ->postJson("/api/v1/projects/{$project->id}/phases/{$phase->id}/tasks", [
                'name' => 'Foundation Task',
                'budget' => 2000,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Foundation Task');
    }

    public function test_update_task_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create(['phase_id' => $phase->id]);

        $response = $this->actingAs($user)
            ->putJson("/api/v1/projects/{$project->id}/phases/{$phase->id}/tasks/{$task->id}", [
                'name' => 'Updated Task',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Task');
    }

    public function test_delete_task_successfully()
    {
        $user = User::factory()->create();
        $project = Project::factory()->create(['customer_id' => $user->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create(['phase_id' => $phase->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/projects/{$project->id}/phases/{$phase->id}/tasks/{$task->id}");

        $response->assertStatus(200);
    }
}
