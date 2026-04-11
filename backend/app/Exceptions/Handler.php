<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * @param  Request  $request
     */
    public function render(mixed $request, Throwable $exception): JsonResponse|Response|\Symfony\Component\HttpFoundation\Response
    {
        if ($this->shouldReturnJson($request, $exception)) {
            if ($exception instanceof ValidationException) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => $exception->getMessage(),
                    'errors' => $exception->errors(),
                ], $exception->status);
            }

            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => $exception->getMessage() ?: 'Unauthenticated',
                    'errors' => [],
                ], 401);
            }

            if ($exception instanceof HttpExceptionInterface) {
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => $exception->getMessage() ?: 'An error occurred',
                    'errors' => [],
                ], $exception->getStatusCode());
            }

            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $exception->getMessage() ?: 'An error occurred',
                'errors' => [],
            ], 500);
        }

        return parent::render($request, $exception);
    }
}
