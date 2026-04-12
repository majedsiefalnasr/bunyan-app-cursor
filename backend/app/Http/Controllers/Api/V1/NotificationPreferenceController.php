<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\UpdateNotificationPreferencesRequest;
use App\Http\Resources\Api\V1\NotificationPreferenceResource;
use App\Services\NotificationPreferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends BaseController
{
    public function __construct(private NotificationPreferenceService $service)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            return $this->unauthorized();
        }

        $items = $this->service->mergedForUser($user);

        return $this->sendSuccess(
            NotificationPreferenceResource::collection($items),
            'تم جلب تفضيلات الإشعارات بنجاح',
            200
        );
    }

    public function update(UpdateNotificationPreferencesRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->service->sync($user, $request->validated('preferences'));

        $items = $this->service->mergedForUser($user);

        return $this->sendSuccess(
            NotificationPreferenceResource::collection($items),
            'تم تحديث تفضيلات الإشعارات بنجاح',
            200
        );
    }
}
