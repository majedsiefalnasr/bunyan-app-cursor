<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProjectStatus;
use App\Http\Requests\Api\V1\CreateProjectRequest;
use App\Http\Requests\Api\V1\TransitionProjectStatusRequest;
use App\Http\Requests\Api\V1\UpdateProjectRequest;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends BaseController
{
    public function __construct(private ProjectService $projectService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $projects = $this->projectService->paginateForUser(
            $request->user(),
            (int) ($request->per_page ?? 15),
            $request->query('status')
        );

        return $this->sendSuccess(
            ProjectResource::collection($projects),
            'تم جلب المشاريع بنجاح',
            200
        );
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        $project = $this->projectService->findForShow($project->id);

        return $this->sendSuccess(
            new ProjectResource($project),
            'تم جلب المشروع بنجاح',
            200
        );
    }

    public function store(CreateProjectRequest $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $project = $this->projectService->create($request->user(), $request->validated());
        $project->load(['customer', 'contractor', 'supervisingArchitect']);
        $project->loadCount(['phases', 'tasks']);

        return $this->sendSuccess(
            new ProjectResource($project),
            'تم إنشاء المشروع بنجاح',
            201
        );
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project = $this->projectService->updateProject($project, $request->validated());
        $project->load(['customer', 'contractor', 'supervisingArchitect']);
        $project->loadCount(['phases', 'tasks']);

        return $this->sendSuccess(
            new ProjectResource($project),
            'تم تحديث المشروع بنجاح',
            200
        );
    }

    public function updateStatus(TransitionProjectStatusRequest $request, Project $project): JsonResponse
    {
        $this->authorize('transitionStatus', $project);

        $next = ProjectStatus::from($request->validated('status'));
        $project = $this->projectService->transitionStatus($project, $next);
        $project->load(['customer', 'contractor', 'supervisingArchitect']);
        $project->loadCount(['phases', 'tasks']);

        return $this->sendSuccess(
            new ProjectResource($project),
            'تم تحديث حالة المشروع بنجاح',
            200
        );
    }

    public function timeline(Request $request, Project $project): JsonResponse
    {
        $this->authorize('view', $project);

        return $this->sendSuccess(
            $this->projectService->timeline($project),
            'تم جلب الجدول الزمني بنجاح',
            200
        );
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $project->delete();

        return $this->sendSuccess(null, 'تم حذف المشروع بنجاح', 200);
    }
}
