<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    public function __construct(private NotificationService $service)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = min(max($request->integer('per_page', 20), 1), 100);
        $paginator = $this->service->list($request->user(), $perPage);

        return $this->sendSuccess(
            NotificationResource::collection($paginator),
            'تم جلب الإشعارات بنجاح',
            200
        );
    }

    public function unreadCount(Request $request): JsonResponse
    {
        $count = $this->service->unreadCount($request->user());

        return $this->sendSuccess(
            ['count' => $count],
            'تم جلب عدد الإشعارات غير المقروءة',
            200
        );
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $updated = $this->service->markRead($request->user(), $id);
        if (! $updated) {
            return $this->notFound();
        }

        return $this->sendSuccess(
            null,
            'تم تحديد الإشعار كمقروء',
            200
        );
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $this->service->markAllRead($request->user());

        return $this->sendSuccess(
            null,
            'تم تحديد جميع الإشعارات كمقروءة',
            200
        );
    }
}
