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

    /**
     * @return array{user: User, project: Project, phase: Phase}
     */
    private function contractorTaskFixture(): array
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $user = User::factory()->create(['role' => 'contractor']);
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $user->id,
        ]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        return compact('user', 'project', 'phase');
    }

    public function test_list_tasks_successfully()
    {
        extract($this->contractorTaskFixture(), EXTR_SKIP);
        Task::factory()->count(3)->create(['phase_id' => $phase->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/projects/{$project->id}/phases/{$phase->id}/tasks");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_show_task_successfully()
    {
        extract($this->contractorTaskFixture(), EXTR_SKIP);
        $task = Task::factory()->create(['phase_id' => $phase->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/projects/{$project->id}/phases/{$phase->id}/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $task->id);
    }

    public function test_create_task_successfully()
    {
        extract($this->contractorTaskFixture(), EXTR_SKIP);

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
        extract($this->contractorTaskFixture(), EXTR_SKIP);
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
        $customer = User::factory()->create(['role' => 'customer']);
        $contractor = User::factory()->create(['role' => 'contractor']);
        $user = User::factory()->create(['role' => 'supervising_architect']);
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
            'supervising_architect_id' => $user->id,
        ]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);
        $task = Task::factory()->create(['phase_id' => $phase->id]);

        $response = $this->actingAs($user)
            ->deleteJson("/api/v1/projects/{$project->id}/phases/{$phase->id}/tasks/{$task->id}");

        $response->assertStatus(200);
    }
}
