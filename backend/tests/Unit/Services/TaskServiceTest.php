<?php

namespace Tests\Unit\Services;

use App\Enums\TaskStatus;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TaskService::class);
    }

    public function test_assert_assignee_belongs_to_project_allows_null_and_blocks_missing_user(): void
    {
        $project = Project::factory()->create();

        $this->service->assertAssigneeBelongsToProject(null, $project);

        $this->expectException(ValidationException::class);
        $this->service->assertAssigneeBelongsToProject(999999, $project);
    }

    public function test_create_for_phase_rejects_assignee_without_view_permission(): void
    {
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        $outsider = User::factory()->fieldEngineer()->create();

        $this->expectException(ValidationException::class);
        $this->service->createForPhase($phase, $customer, [
            'name' => 'مهمة',
            'assigned_to' => $outsider->id,
        ]);
    }

    public function test_update_task_normalizes_name_and_title_ar_and_blocks_invalid_status_transition(): void
    {
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->create(['customer_id' => $customer->id]);
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        /** @var Task $task */
        $task = Task::query()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'name' => 'old',
            'title_ar' => 'old',
            'title_en' => null,
            'description' => null,
            'status' => TaskStatus::Todo,
            'priority' => 'medium',
            'budget' => 0,
            'assigned_to' => null,
            'sort_order' => 0,
            'created_by' => $customer->id,
        ]);

        $updated = $this->service->updateTask($task, ['name' => 'اسم جديد']);
        $this->assertSame('اسم جديد', $updated->name);
        $this->assertSame('اسم جديد', $updated->title_ar);

        $this->expectException(ValidationException::class);
        $this->service->updateTask($task->fresh(), ['status' => TaskStatus::Done->value]);
    }

    public function test_transition_status_allows_valid_and_rejects_invalid(): void
    {
        $actor = User::factory()->admin()->create();
        $project = Project::factory()->create();
        $phase = Phase::factory()->create(['project_id' => $project->id]);

        $task = Task::query()->create([
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'name' => 't',
            'title_ar' => 't',
            'status' => TaskStatus::Todo,
            'priority' => 'medium',
            'budget' => 0,
            'assigned_to' => null,
            'sort_order' => 0,
            'created_by' => $actor->id,
        ]);

        $moved = $this->service->transitionStatus($task, TaskStatus::InProgress->value, $actor);
        $this->assertSame(TaskStatus::InProgress, $moved->status);

        $this->expectException(ValidationException::class);
        $this->service->transitionStatus($moved->fresh(), TaskStatus::Done->value, $actor);
    }
}
