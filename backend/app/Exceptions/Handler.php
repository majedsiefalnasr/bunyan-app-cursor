<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function render(Request $request, Throwable $exception): JsonResponse|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
    {
        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $exception->getMessage() ?: 'An error occurred',
                'errors' => [],
            ], status: $this->getHttpStatusCode($exception));
        }

        return parent::render($request, $exception);
    }

    private function getHttpStatusCode(Throwable $exception): int
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        return 500;
    }
}
