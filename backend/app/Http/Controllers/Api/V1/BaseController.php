<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class BaseController extends Controller
{
    use AuthorizesRequests;

    public function sendSuccess($data = null, $message = 'Operation successful', $statusCode = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'errors' => [],
        ], $statusCode);
    }

    public function sendError($message = 'Error occurred', $errors = [], $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }

    public function notFound(): JsonResponse
    {
        return $this->sendError('المورد المطلوب غير موجود', [], 404);
    }

    public function unauthorized(): JsonResponse
    {
        return $this->sendError('غير مصرح', [], 401);
    }

    public function forbidden(): JsonResponse
    {
        return $this->sendError('لا توجد صلاحيات كافية', [], 403);
    }

    public function validationError($errors = []): JsonResponse
    {
        return $this->sendError('خطأ في التحقق من البيانات', $errors, 422);
    }
}
