<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CreateTaskRequest;
use App\Http\Requests\Api\V1\UpdateTaskRequest;
use App\Http\Resources\Api\V1\TaskResource;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends BaseController
{
    public function __construct(private TaskService $taskService)
    {
    }

    public function index(Project $project, Phase $phase, Request $request): JsonResponse
    {
        if ($phase->project_id !== $project->id) {
            return $this->notFound();
        }

        $tasks = $phase->tasks()->paginate($request->per_page ?? 15);

        return $this->sendSuccess(
            TaskResource::collection($tasks),
            'تم جلب المهام بنجاح',
            200
        );
    }

    public function show(Project $project, Phase $phase, Task $task): JsonResponse
    {
        if ($phase->project_id !== $project->id || $task->phase_id !== $phase->id) {
            return $this->notFound();
        }

        return $this->sendSuccess(
            new TaskResource($task->load(['assignee', 'phase', 'project', 'comments.user'])),
            'تم جلب المهمة بنجاح',
            200
        );
    }

    public function store(Project $project, Phase $phase, CreateTaskRequest $request): JsonResponse
    {
        if ($phase->project_id !== $project->id) {
            return $this->notFound();
        }

        $this->authorize('create', [Task::class, $phase]);

        $task = $this->taskService->createForPhase($phase, $request->user(), $request->validated());

        return $this->sendSuccess(
            new TaskResource($task->load(['assignee', 'phase', 'comments.user'])),
            'تم إنشاء المهمة بنجاح',
            201
        );
    }

    public function update(Project $project, Phase $phase, Task $task, UpdateTaskRequest $request): JsonResponse
    {
        if ($phase->project_id !== $project->id || $task->phase_id !== $phase->id) {
            return $this->notFound();
        }

        $this->authorize('update', $task);

        $task = $this->taskService->updateTask($task, $request->validated());

        return $this->sendSuccess(
            new TaskResource($task),
            'تم تحديث المهمة بنجاح',
            200
        );
    }

    public function destroy(Project $project, Phase $phase, Task $task, Request $request): JsonResponse
    {
        if ($phase->project_id !== $project->id || $task->phase_id !== $phase->id) {
            return $this->notFound();
        }

        $this->authorize('delete', $task);

        $task->delete();

        return $this->sendSuccess(null, 'تم حذف المهمة بنجاح', 200);
    }
}
