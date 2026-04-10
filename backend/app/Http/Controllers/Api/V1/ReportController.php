<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\CreateReportRequest;
use App\Http\Requests\Api\V1\UpdateReportRequest;
use App\Http\Resources\Api\V1\ReportResource;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Report::query();

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->paginate($request->per_page ?? 15);

        return $this->sendSuccess(
            ReportResource::collection($reports),
            'تم جلب التقارير بنجاح',
            200
        );
    }

    public function show(Report $report): JsonResponse
    {
        return $this->sendSuccess(
            new ReportResource($report),
            'تم جلب التقرير بنجاح',
            200
        );
    }

    public function store(CreateReportRequest $request): JsonResponse
    {
        $report = Report::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'content' => $request->content,
            'description' => $request->content,
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        return $this->sendSuccess(
            new ReportResource($report),
            'تم إنشاء التقرير بنجاح',
            201
        );
    }

    public function update(UpdateReportRequest $request, Report $report): JsonResponse
    {
        $this->authorize('update', $report);

        $report->update($request->validated());

        return $this->sendSuccess(
            new ReportResource($report),
            'تم تحديث التقرير بنجاح',
            200
        );
    }

    public function destroy(Request $request, Report $report): JsonResponse
    {
        $this->authorize('delete', $report);

        $report->delete();

        return $this->sendSuccess(null, 'تم حذف التقرير بنجاح', 200);
    }
}
