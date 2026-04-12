<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class HealthController extends BaseController
{
    public function __invoke(Request $request): JsonResponse
    {
        $correlationId = $request->attributes->get('correlation_id');

        return $this->sendSuccess([
            'service' => 'bunyan-api',
            'version' => 'v1',
            'time' => now()->toIso8601String(),
            'app' => config('app.name'),
            'correlation_id' => is_string($correlationId) ? $correlationId : null,
        ]);
    }
}
