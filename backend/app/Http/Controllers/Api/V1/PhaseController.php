<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CreatePhaseRequest;
use App\Http\Requests\Api\V1\UpdatePhaseRequest;
use App\Http\Resources\Api\V1\PhaseResource;
use App\Models\Phase;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhaseController extends BaseController
{
    public function index(Project $project, Request $request): JsonResponse
    {
        $phases = $project->phases()->paginate($request->per_page ?? 15);

        return $this->sendSuccess(
            PhaseResource::collection($phases),
            'تم جلب المراحل بنجاح',
            200
        );
    }

    public function show(Project $project, Phase $phase): JsonResponse
    {
        if ($phase->project_id !== $project->id) {
            return $this->notFound();
        }

        return $this->sendSuccess(
            new PhaseResource($phase),
            'تم جلب المرحلة بنجاح',
            200
        );
    }

    public function store(Project $project, CreatePhaseRequest $request): JsonResponse
    {
        $this->authorize('create', [Phase::class, $project]);

        $phase = Phase::create([
            'project_id' => $project->id,
            'name' => $request->name,
            'description' => $request->description,
            'status' => 'pending',
            'budget' => $request->budget,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return $this->sendSuccess(
            new PhaseResource($phase),
            'تم إنشاء المرحلة بنجاح',
            201
        );
    }

    public function update(Project $project, Phase $phase, UpdatePhaseRequest $request): JsonResponse
    {
        if ($phase->project_id !== $project->id) {
            return $this->notFound();
        }

        $this->authorize('update', $phase);

        $phase->update($request->validated());

        return $this->sendSuccess(
            new PhaseResource($phase),
            'تم تحديث المرحلة بنجاح',
            200
        );
    }

    public function destroy(Project $project, Phase $phase, Request $request): JsonResponse
    {
        if ($phase->project_id !== $project->id) {
            return $this->notFound();
        }

        $this->authorize('delete', $phase);

        $phase->delete();

        return $this->sendSuccess(null, 'تم حذف المرحلة بنجاح', 200);
    }
}
