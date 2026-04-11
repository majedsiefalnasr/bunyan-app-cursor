<?php

namespace App\Http\Controllers\Api;

use App\Enums\ErrorCode;
use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function sendSuccess(mixed $data = null, string $message = '', int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message !== '' ? $message : null,
            'errors' => [],
            'error' => null,
        ], $statusCode);
    }

    protected function sendError(
        string $code,
        string $message,
        ?array $details = null,
        int $statusCode = 400,
    ): JsonResponse {
        $enum = ErrorCode::tryFrom($code);

        return response()->json([
            'success' => false,
            'data' => null,
            'message' => null,
            'errors' => [],
            'error' => [
                'code' => $enum?->value ?? $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $statusCode);
    }
}
