<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException as IlluminateValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

final class ApiExceptionRenderer
{
    public static function render(Request $request, Throwable $e): ?JsonResponse
    {
        if (! ($request->is('api/*') || $request->expectsJson())) {
            return null;
        }

        if ($e instanceof IlluminateValidationException) {
            return ApiErrorResponse::json(
                $request,
                ErrorCode::VALIDATION_ERROR,
                ApiErrorResponse::trans($request, ErrorCode::VALIDATION_ERROR),
                $e->errors(),
                ErrorCode::VALIDATION_ERROR->httpStatus(),
            );
        }

        if ($e instanceof AuthenticationException) {
            $code = self::authenticationErrorCode($e);

            return ApiErrorResponse::json(
                $request,
                $code,
                ApiErrorResponse::trans($request, $code),
                null,
                $code->httpStatus(),
            );
        }

        if ($e instanceof AuthorizationException) {
            return ApiErrorResponse::json(
                $request,
                ErrorCode::RBAC_ROLE_DENIED,
                ApiErrorResponse::trans($request, ErrorCode::RBAC_ROLE_DENIED),
                null,
                ErrorCode::RBAC_ROLE_DENIED->httpStatus(),
            );
        }

        if ($e instanceof ModelNotFoundException) {
            return ApiErrorResponse::json(
                $request,
                ErrorCode::RESOURCE_NOT_FOUND,
                ApiErrorResponse::trans($request, ErrorCode::RESOURCE_NOT_FOUND),
                [
                    'resource' => class_basename((string) $e->getModel()),
                    'id' => $e->getIds()[0] ?? null,
                ],
                ErrorCode::RESOURCE_NOT_FOUND->httpStatus(),
            );
        }

        if ($e instanceof ThrottleRequestsException) {
            $headers = $e->getHeaders();
            $retryHeader = $headers['Retry-After'] ?? null;
            $retryAfter = is_array($retryHeader) ? (int) ($retryHeader[0] ?? null) : (int) ($retryHeader ?? 0);
            if ($retryAfter === 0) {
                $retryAfter = null;
            }

            return ApiErrorResponse::json(
                $request,
                ErrorCode::RATE_LIMIT_EXCEEDED,
                ApiErrorResponse::trans($request, ErrorCode::RATE_LIMIT_EXCEEDED),
                array_filter(['retry_after' => $retryAfter], static fn ($v) => $v !== null),
                ErrorCode::RATE_LIMIT_EXCEEDED->httpStatus(),
            );
        }

        if ($e instanceof DomainException) {
            $code = ErrorCode::tryFrom($e->getErrorCode()) ?? ErrorCode::SERVER_ERROR;

            return ApiErrorResponse::json(
                $request,
                $code,
                $e->getMessage(),
                $e->getDetails(),
                $e->getHttpStatus(),
                $e,
            );
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();

            if ($status === 503) {
                return ApiErrorResponse::json(
                    $request,
                    ErrorCode::SERVICE_UNAVAILABLE,
                    ApiErrorResponse::trans($request, ErrorCode::SERVICE_UNAVAILABLE),
                    null,
                    ErrorCode::SERVICE_UNAVAILABLE->httpStatus(),
                    $e,
                );
            }

            if ($status === 403) {
                return ApiErrorResponse::json(
                    $request,
                    ErrorCode::RBAC_ROLE_DENIED,
                    ApiErrorResponse::trans($request, ErrorCode::RBAC_ROLE_DENIED),
                    null,
                    ErrorCode::RBAC_ROLE_DENIED->httpStatus(),
                    $e,
                );
            }

            if ($status === 404) {
                return ApiErrorResponse::json(
                    $request,
                    ErrorCode::RESOURCE_NOT_FOUND,
                    ApiErrorResponse::trans($request, ErrorCode::RESOURCE_NOT_FOUND),
                    null,
                    ErrorCode::RESOURCE_NOT_FOUND->httpStatus(),
                    $e,
                );
            }

            return ApiErrorResponse::json(
                $request,
                ErrorCode::SERVER_ERROR,
                ApiErrorResponse::trans($request, ErrorCode::SERVER_ERROR),
                null,
                $status >= 400 && $status < 600 ? $status : ErrorCode::SERVER_ERROR->httpStatus(),
                $e,
            );
        }

        Log::error('api.unhandled_exception', [
            'correlation_id' => ApiErrorResponse::correlationId($request),
            'exception' => $e::class,
            'message' => $e->getMessage(),
        ]);

        return ApiErrorResponse::json(
            $request,
            ErrorCode::SERVER_ERROR,
            ApiErrorResponse::trans($request, ErrorCode::SERVER_ERROR),
            null,
            ErrorCode::SERVER_ERROR->httpStatus(),
            $e,
        );
    }

    private static function authenticationErrorCode(AuthenticationException $e): ErrorCode
    {
        $message = strtolower($e->getMessage());

        if (str_contains($message, 'expire')) {
            return ErrorCode::AUTH_TOKEN_EXPIRED;
        }

        return ErrorCode::AUTH_UNAUTHORIZED;
    }
}
