<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\DashboardIndexRequest;
use App\Http\Requests\Api\V1\DashboardRecentActivityRequest;
use App\Http\Resources\Api\V1\ActivityLogResource;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DashboardController extends BaseController
{
    public function __construct(private DashboardService $dashboardService)
    {
    }

    public function index(DashboardIndexRequest $request): JsonResponse
    {
        $payload = $this->dashboardService->getOverview($request->user());

        return $this->sendSuccess(
            $payload,
            'تم جلب لوحة التحكم بنجاح',
            Response::HTTP_OK,
        );
    }

    public function metrics(DashboardIndexRequest $request): JsonResponse
    {
        $payload = $this->dashboardService->getMetrics($request->user());

        return $this->sendSuccess(
            $payload,
            'تم جلب مؤشرات الأداء بنجاح',
            Response::HTTP_OK,
        );
    }

    public function recentActivity(DashboardRecentActivityRequest $request): JsonResponse
    {
        $perPage = (int) $request->validated('per_page');
        $paginator = $this->dashboardService->getRecentActivity($request->user(), $perPage);

        return $this->sendSuccess(
            ActivityLogResource::collection($paginator)->response()->getData(true),
            'تم جلب النشاط الأخير بنجاح',
            Response::HTTP_OK,
        );
    }
}
