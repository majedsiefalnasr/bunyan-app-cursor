<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\StoreProjectTaskRequest;
use App\Http\Resources\Api\V1\TaskResource;
use App\Models\Phase;
use App\Models\Project;
use App\Models\Task;
use App\Repositories\TaskRepository;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectTaskController extends BaseController
{
    public function __construct(
        private TaskRepository $repository,
        private TaskService $service,
    ) {
    }

    public function all(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Task::query();

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $tasks = $query->paginate($request->per_page ?? 15);

        return $this->sendSuccess(
            TaskResource::collection($tasks),
            'تم جلب المهام بنجاح',
            200
        );
    }

    public function index(Project $project, Request $request): JsonResponse
    {
        $this->authorize('view', $project);

        $tasks = $this->repository->paginateForProject($project->id, [
            'per_page' => $request->query('per_page'),
            'phase_id' => $request->query('phase_id'),
            'status' => $request->query('status'),
            'priority' => $request->query('priority'),
            'assigned_to' => $request->query('assigned_to'),
        ]);

        return $this->sendSuccess(
            TaskResource::collection($tasks),
            'تم جلب المهام بنجاح',
            200
        );
    }

    public function store(Project $project, StoreProjectTaskRequest $request): JsonResponse
    {
        $this->authorize('view', $project);

        $phase = Phase::query()
            ->where('project_id', $project->id)
            ->whereKey($request->validated('phase_id'))
            ->firstOrFail();

        $this->authorize('create', [Task::class, $phase]);

        $task = $this->service->createForProject($project, $request->user(), $request->validated());
        $task->load(['assignee', 'phase', 'comments.user']);

        return $this->sendSuccess(
            new TaskResource($task),
            'تم إنشاء المهمة بنجاح',
            201
        );
    }
}
