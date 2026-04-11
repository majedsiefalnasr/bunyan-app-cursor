<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
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
    public function render(mixed $request, Throwable $exception): JsonResponse|Response|SymfonyResponse
    {
        $apiResponse = ApiExceptionRenderer::render($request, $exception);
        if ($apiResponse !== null) {
            return $apiResponse;
        }

        return parent::render($request, $exception);
    }
}
