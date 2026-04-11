<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Controllers\Api\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use ApiResponse;
    use AuthorizesRequests;

    public function notFound(): JsonResponse
    {
        return $this->sendError(
            ErrorCode::RESOURCE_NOT_FOUND->value,
            __('errors.codes.'.ErrorCode::RESOURCE_NOT_FOUND->value.'.message', [], 'ar'),
            null,
            ErrorCode::RESOURCE_NOT_FOUND->httpStatus(),
        );
    }

    public function unauthorized(): JsonResponse
    {
        return $this->sendError(
            ErrorCode::AUTH_UNAUTHORIZED->value,
            __('errors.codes.'.ErrorCode::AUTH_UNAUTHORIZED->value.'.message', [], 'ar'),
            null,
            ErrorCode::AUTH_UNAUTHORIZED->httpStatus(),
        );
    }

    public function forbidden(): JsonResponse
    {
        return $this->sendError(
            ErrorCode::RBAC_ROLE_DENIED->value,
            __('errors.codes.'.ErrorCode::RBAC_ROLE_DENIED->value.'.message', [], 'ar'),
            null,
            ErrorCode::RBAC_ROLE_DENIED->httpStatus(),
        );
    }

    public function validationError($errors = []): JsonResponse
    {
        return $this->sendError(
            ErrorCode::VALIDATION_ERROR->value,
            __('errors.codes.'.ErrorCode::VALIDATION_ERROR->value.'.message', [], 'ar'),
            is_array($errors) ? $errors : [],
            ErrorCode::VALIDATION_ERROR->httpStatus(),
        );
    }
}
