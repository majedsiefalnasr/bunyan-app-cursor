<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\AssignTaskRequest;
use App\Http\Requests\Api\V1\StoreTaskCommentRequest;
use App\Http\Requests\Api\V1\TransitionTaskStatusRequest;
use App\Http\Requests\Api\V1\UpdateWorkspaceTaskRequest;
use App\Http\Resources\Api\V1\TaskCommentResource;
use App\Http\Resources\Api\V1\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;

class TaskWorkspaceController extends BaseController
{
    public function __construct(private TaskService $tasks)
    {
    }

    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);
        $task->load(['comments.user', 'assignee', 'phase', 'project']);

        return $this->sendSuccess(
            new TaskResource($task),
            'تم جلب المهمة بنجاح',
            200
        );
    }

    public function update(Task $task, UpdateWorkspaceTaskRequest $request): JsonResponse
    {
        $this->authorize('update', $task);
        $task = $this->tasks->updateTask($task, $request->validated());

        return $this->sendSuccess(
            new TaskResource($task),
            'تم تحديث المهمة بنجاح',
            200
        );
    }

    public function assign(Task $task, AssignTaskRequest $request): JsonResponse
    {
        $this->authorize('update', $task);
        $assignedTo = $request->validated('assigned_to') ?? null;
        $task = $this->tasks->assign($task, $assignedTo, $request->user());

        return $this->sendSuccess(
            new TaskResource($task),
            'تم تعيين المهمة بنجاح',
            200
        );
    }

    public function transitionStatus(Task $task, TransitionTaskStatusRequest $request): JsonResponse
    {
        $this->authorize('update', $task);
        $task = $this->tasks->transitionStatus($task, $request->validated('status'), $request->user());

        return $this->sendSuccess(
            new TaskResource($task),
            'تم تحديث حالة المهمة بنجاح',
            200
        );
    }

    public function storeComment(Task $task, StoreTaskCommentRequest $request): JsonResponse
    {
        $this->authorize('update', $task);
        $comment = $this->tasks->addComment($task, $request->user(), $request->validated('body'));
        $comment->load('user');

        return $this->sendSuccess(
            new TaskCommentResource($comment),
            'تمت إضافة التعليق',
            201
        );
    }
}
