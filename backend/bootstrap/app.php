<?php

use App\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\ErrorDetailFiltering;
use App\Http\Middleware\InjectCorrelationId;
use App\Http\Middleware\LogApiActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(InjectCorrelationId::class);
        $middleware->appendToGroup('api', LogApiActivity::class);
        $middleware->appendToGroup('api', ErrorDetailFiltering::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            return ApiExceptionRenderer::render($request, $e);
        });
    })->create();
