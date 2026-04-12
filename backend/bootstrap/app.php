<?php

use App\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\ErrorDetailFiltering;
use App\Http\Middleware\InjectCorrelationId;
use App\Http\Middleware\LogApiActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function (...$_): void {
            // Plain 200 for CI / local readiness. Hidden outside local, testing, or `CI=true` (GitHub Actions).
            Route::get('/__ci_ready', function () {
                $ci = filter_var(getenv('CI') ?: ($_SERVER['CI'] ?? ''), FILTER_VALIDATE_BOOLEAN);
                abort_unless(app()->environment(['local', 'testing']) || $ci, 404);

                return response('ok', 200);
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->prepend(InjectCorrelationId::class);
        $middleware->appendToGroup('api', LogApiActivity::class);
        $middleware->appendToGroup('api', ErrorDetailFiltering::class);
        $middleware->alias([
            'role' => CheckRole::class,
            'permission' => CheckPermission::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, Request $request) {
            return ApiExceptionRenderer::render($request, $e);
        });
    })->create();
