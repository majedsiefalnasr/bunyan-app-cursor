<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Throwable;

final class ApiErrorResponse
{
    public static function json(
        Request $request,
        ErrorCode $code,
        string $message,
        ?array $details,
        int $status,
        ?Throwable $throwable = null,
    ): JsonResponse {
        $correlationId = self::correlationId($request);
        $debugDetails = self::debugDetails($request, $throwable);

        $payloadDetails = $details;
        if ($debugDetails !== null) {
            $payloadDetails ??= [];
            $payloadDetails['_debug'] = $debugDetails;
        }

        $response = response()->json([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => $code->value,
                'message' => $message,
                'details' => $payloadDetails,
            ],
        ], $status);

        return $response->withHeaders([
            'X-Correlation-ID' => $correlationId,
        ]);
    }

    public static function correlationId(Request $request): string
    {
        $existing = $request->attributes->get('correlation_id');
        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $header = $request->headers->get('X-Correlation-ID');
        if (is_string($header) && $header !== '') {
            return $header;
        }

        return sprintf('req_%d_%s', (int) (microtime(true) * 1000), bin2hex(random_bytes(4)));
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function debugDetails(Request $request, ?Throwable $throwable): ?array
    {
        if ($throwable === null) {
            return null;
        }

        $user = $request->user();
        if (! $user instanceof User || $user->role !== UserRole::Admin) {
            return null;
        }

        if (! App::environment('local', 'testing')) {
            return null;
        }

        return [
            'exception' => $throwable::class,
            'file' => $throwable->getFile(),
            'line' => $throwable->getLine(),
            'trace' => $throwable->getTraceAsString(),
        ];
    }

    public static function trans(Request $request, ErrorCode $code, string $key = 'message'): string
    {
        $locale = $request->getPreferredLanguage(['ar', 'en']) ?: 'ar';

        return Lang::get("errors.codes.{$code->value}.{$key}", [], $locale);
    }
}
