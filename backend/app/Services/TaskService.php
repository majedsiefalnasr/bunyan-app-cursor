<?php

namespace App\Services;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TaskService
{
    public function assertAssigneeBelongsToProject(?int $userId, Project $project): void
    {
        if ($userId === null) {
            return;
        }
        $user = User::query()->find($userId);
        if (! $user) {
            throw ValidationException::withMessages(['assigned_to' => ['المستخدم غير موجود']]);
        }
        if (! Gate::forUser($user)->allows('view', $project)) {
            throw ValidationException::withMessages(['assigned_to' => ['لا يمكن تعيين هذه المهمة لهذا المستخدم']]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createForPhase(Phase $phase, User $actor, array $data): Task
    {
        $project = $phase->project;
        $this->assertAssigneeBelongsToProject(isset($data['assigned_to']) ? (int) $data['assigned_to'] : null, $project);

        $titleAr = (string) ($data['title_ar'] ?? $data['name'] ?? '');
        $name = (string) ($data['name'] ?? $titleAr);

        $payload = [
            'project_id' => $project->id,
            'phase_id' => $phase->id,
            'name' => $name,
            'title_ar' => $titleAr,
            'title_en' => $data['title_en'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => TaskStatus::Todo,
            'priority' => $data['priority'] ?? TaskPriority::Medium->value,
            'budget' => $data['budget'] ?? 0,
            'assigned_to' => $data['assigned_to'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'end_date' => $data['end_date'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'estimated_hours' => $data['estimated_hours'] ?? null,
            'actual_hours' => $data['actual_hours'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'created_by' => $actor->id,
        ];

        return Task::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createForProject(Project $project, User $actor, array $data): Task
    {
        $phase = Phase::query()
            ->where('project_id', $project->id)
            ->whereKey($data['phase_id'])
            ->firstOrFail();

        return $this->createForPhase($phase, $actor, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateTask(Task $task, array $data): Task
    {
        $task->loadMissing('project', 'phase.project');
        $project = $task->project ?? $task->phase->project;

        if (array_key_exists('assigned_to', $data)) {
            $this->assertAssigneeBelongsToProject(
                $data['assigned_to'] !== null ? (int) $data['assigned_to'] : null,
                $project
            );
        }

        $updates = collect($data)->only([
            'name',
            'title_ar',
            'title_en',
            'description',
            'budget',
            'assigned_to',
            'start_date',
            'end_date',
            'due_date',
            'estimated_hours',
            'actual_hours',
            'priority',
            'sort_order',
            'status',
            'phase_id',
        ])->filter(fn ($v, $k) => $k === 'assigned_to' || $v !== null)->all();

        if (isset($updates['phase_id'])) {
            $phase = Phase::query()
                ->where('project_id', $project->id)
                ->whereKey($updates['phase_id'])
                ->firstOrFail();
            $updates['phase_id'] = $phase->id;
        }

        if (isset($updates['name']) && ! isset($updates['title_ar'])) {
            $updates['title_ar'] = $updates['name'];
        }
        if (isset($updates['title_ar']) && ! isset($updates['name'])) {
            $updates['name'] = $updates['title_ar'];
        }

        if (isset($updates['status'])) {
            /** @var TaskStatus $from */
            $from = $task->status;
            $to = TaskStatus::from((string) $updates['status']);
            if (! $this->canTransition($from, $to)) {
                throw ValidationException::withMessages(['status' => ['انتقال الحالة غير مسموح']]);
            }
        }

        $task->fill($updates);
        $task->save();

        return $task->fresh(['phase', 'project', 'assignee', 'comments.user']);
    }

    public function assign(Task $task, ?int $assignedTo, User $actor): Task
    {
        $task->loadMissing('project', 'phase.project');
        $project = $task->project ?? $task->phase->project;
        $this->assertAssigneeBelongsToProject($assignedTo, $project);
        $task->assigned_to = $assignedTo;
        $task->save();

        Log::info('Task assigned', [
            'action' => 'task.assigned',
            'task_id' => $task->id,
            'user_id' => $actor->id,
            'assigned_to' => $assignedTo,
        ]);

        return $task->fresh(['phase', 'project', 'assignee', 'comments.user']);
    }

    public function transitionStatus(Task $task, string $newStatus, User $actor): Task
    {
        /** @var TaskStatus $from */
        $from = $task->status;
        $to = TaskStatus::from($newStatus);
        if (! $this->canTransition($from, $to)) {
            throw ValidationException::withMessages(['status' => ['انتقال الحالة غير مسموح']]);
        }
        $task->status = $to;
        $task->save();

        Log::info('Workflow transition', [
            'action' => 'task.status',
            'entity_type' => 'task',
            'entity_id' => $task->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'user_id' => $actor->id,
        ]);

        return $task->fresh(['phase', 'project', 'assignee', 'comments.user']);
    }

    private function canTransition(TaskStatus $from, TaskStatus $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return match ($from) {
            TaskStatus::Todo => in_array($to, [TaskStatus::InProgress, TaskStatus::Blocked], true),
            TaskStatus::InProgress => in_array($to, [TaskStatus::InReview, TaskStatus::Blocked, TaskStatus::Todo], true),
            TaskStatus::InReview => in_array($to, [TaskStatus::Done, TaskStatus::Blocked, TaskStatus::InProgress], true),
            TaskStatus::Done => false,
            TaskStatus::Blocked => in_array($to, [TaskStatus::Todo, TaskStatus::InProgress], true),
        };
    }

    public function addComment(Task $task, User $actor, string $body): TaskComment
    {
        return TaskComment::query()->create([
            'task_id' => $task->id,
            'user_id' => $actor->id,
            'body' => $body,
        ]);
    }
}
