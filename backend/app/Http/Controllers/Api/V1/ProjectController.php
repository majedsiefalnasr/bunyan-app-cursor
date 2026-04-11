<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CreateProjectRequest;
use App\Http\Requests\Api\V1\UpdateProjectRequest;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::query();

        if ($request->user()->role !== 'admin') {
            $query->forUser($request->user());
        }

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        $projects = $query->paginate($request->per_page ?? 15);

        return $this->sendSuccess(
            ProjectResource::collection($projects),
            'تم جلب المشاريع بنجاح',
            200
        );
    }

    public function show(Project $project): JsonResponse
    {
        return $this->sendSuccess(
            new ProjectResource($project),
            'تم جلب المشروع بنجاح',
            200
        );
    }

    public function store(CreateProjectRequest $request): JsonResponse
    {
        $project = Project::create([
            'name' => $request->name,
            'description' => $request->description,
            'customer_id' => $request->user()->id,
            'status' => 'draft',
            'budget' => $request->budget,
            'location' => $request->location,
            'start_date' => $request->start_date,
        ]);

        return $this->sendSuccess(
            new ProjectResource($project),
            'تم إنشاء المشروع بنجاح',
            201
        );
    }

    public function update(UpdateProjectRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $project->update($request->validated());

        return $this->sendSuccess(
            new ProjectResource($project),
            'تم تحديث المشروع بنجاح',
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
