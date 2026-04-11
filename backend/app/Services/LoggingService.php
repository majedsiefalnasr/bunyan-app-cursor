<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class LoggingService
{
    public function logRequest(Request $request, array $context = []): void
    {
        Log::info('request', array_merge($this->baseContext($request), $context));
    }

    public function logResponse(Request $request, int $status, float $durationMs, array $context = []): void
    {
        Log::info('response', array_merge($this->baseContext($request), [
            'response_status' => $status,
            'duration_ms' => $durationMs,
        ], $context));
    }

    public function logError(Request $request, Throwable $e, array $context = []): void
    {
        Log::error('error', array_merge($this->baseContext($request), [
            'exception' => $e::class,
            'message' => $e->getMessage(),
        ], $context));
    }

    public function logActivity(Request $request, string $action, array $context = []): void
    {
        Log::info($action, array_merge($this->baseContext($request), $context));
    }

    /**
     * @return array<string, mixed>
     */
    private function baseContext(Request $request): array
    {
        return [
            'correlation_id' => $request->attributes->get('correlation_id'),
        ];
    }
}
