<?php

namespace Tests\Feature\Api\V1;

use App\Enums\TaskStatus;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTaskApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{contractor: User, architect: User, customer: User, project: Project, phase: Phase}
     */
    private function projectFixture(): array
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $contractor = User::factory()->create(['role' => 'contractor']);
        $architect = User::factory()->create(['role' => 'supervising_architect']);
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
            'contractor_id' => $contractor->id,
            'supervising_architect_id' => $architect->id,
        ]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        return [
            'contractor' => $contractor,
            'architect' => $architect,
            'customer' => $customer,
            'project' => $project,
            'phase' => $phase,
        ];
    }

    public function test_list_project_tasks_successfully(): void
    {
        extract($this->projectFixture(), EXTR_SKIP);
        Task::factory()->count(2)->create(['phase_id' => $phase->id]);

        $response = $this->actingAs($contractor)
            ->getJson("/api/v1/projects/{$project->id}/tasks");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_create_project_task_as_contractor(): void
    {
        extract($this->projectFixture(), EXTR_SKIP);

        $response = $this->actingAs($contractor)
            ->postJson("/api/v1/projects/{$project->id}/tasks", [
                'phase_id' => $phase->id,
                'title_ar' => 'مهمة اختبار',
                'budget' => 100,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title_ar', 'مهمة اختبار')
            ->assertJsonPath('data.status', TaskStatus::Todo->value);
    }

    public function test_create_project_task_as_supervising_architect(): void
    {
        extract($this->projectFixture(), EXTR_SKIP);

        $response = $this->actingAs($architect)
            ->postJson("/api/v1/projects/{$project->id}/tasks", [
                'phase_id' => $phase->id,
                'title_ar' => 'مراجعة',
            ]);

        $response->assertStatus(201);
    }

    public function test_customer_cannot_create_project_task(): void
    {
        extract($this->projectFixture(), EXTR_SKIP);

        $response = $this->actingAs($customer)
            ->postJson("/api/v1/projects/{$project->id}/tasks", [
                'phase_id' => $phase->id,
                'title_ar' => 'لا يجوز',
            ]);

        $response->assertStatus(403);
    }

    public function test_task_workspace_show_and_status_transition(): void
    {
        extract($this->projectFixture(), EXTR_SKIP);
        $task = Task::factory()->create(['phase_id' => $phase->id]);

        $show = $this->actingAs($contractor)->getJson("/api/v1/tasks/{$task->id}");
        $show->assertStatus(200)->assertJsonPath('data.id', $task->id);

        $put = $this->actingAs($contractor)
            ->putJson("/api/v1/tasks/{$task->id}/status", ['status' => TaskStatus::InProgress->value]);

        $put->assertStatus(200)->assertJsonPath('data.status', TaskStatus::InProgress->value);
    }

    public function test_add_comment_on_task(): void
    {
        extract($this->projectFixture(), EXTR_SKIP);
        $task = Task::factory()->create(['phase_id' => $phase->id]);

        $response = $this->actingAs($architect)
            ->postJson("/api/v1/tasks/{$task->id}/comments", [
                'body' => 'تعليق تجريبي',
            ]);

        $response->assertStatus(201)->assertJsonPath('data.body', 'تعليق تجريبي');
    }
}
