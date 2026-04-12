<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\ActivityLogIndexRequest;
use App\Http\Requests\Api\V1\ActivitySubjectIndexRequest;
use App\Http\Resources\Api\V1\ActivityLogResource;
use App\Models\Order;
use App\Models\Project;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ActivityLogController extends BaseController
{
    public function __construct(private ActivityLogService $activityLogService)
    {
    }

    public function adminIndex(ActivityLogIndexRequest $request): JsonResponse
    {
        $paginator = $this->activityLogService->paginateAdmin($request->validated());

        return $this->sendSuccess(
            ActivityLogResource::collection($paginator)->response()->getData(true),
            'تم جلب سجل النشاط بنجاح',
            Response::HTTP_OK,
        );
    }

    public function forSubject(ActivitySubjectIndexRequest $request, string $entity, string $id): JsonResponse
    {
        $model = $this->resolveSubject($entity, $id);
        if ($model === null) {
            return $this->notFound();
        }

        $this->authorize('view', $model);

        $paginator = $this->activityLogService->paginateForSubject($model, $request->validated());

        return $this->sendSuccess(
            ActivityLogResource::collection($paginator)->response()->getData(true),
            'تم جلب النشاط بنجاح',
            Response::HTTP_OK,
        );
    }

    private function resolveSubject(string $entity, string $id): ?Model
    {
        if (! ctype_digit($id)) {
            return null;
        }

        $class = match ($entity) {
            'projects' => Project::class,
            'orders' => Order::class,
            default => null,
        };

        if ($class === null) {
            return null;
        }

        /** @var Model|null */
        return $class::query()->find((int) $id);
    }
}
