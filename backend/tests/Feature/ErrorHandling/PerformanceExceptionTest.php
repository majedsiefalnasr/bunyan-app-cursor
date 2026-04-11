<?php

namespace Tests\Feature\ErrorHandling;

use App\Exceptions\ApiExceptionRenderer;
use App\Http\Middleware\InjectCorrelationId;
use Illuminate\Http\Request;
use Tests\TestCase;

class PerformanceExceptionTest extends TestCase
{
    public function test_correlation_id_middleware_overhead_is_small(): void
    {
        $request = Request::create('/api/v1/ping', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $middleware = new InjectCorrelationId;

        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $middleware->handle($request, fn () => response('ok'));
        }
        $avgMs = ((microtime(true) - $start) * 1000) / 1000;

        $this->assertLessThan(15.0, $avgMs);
    }

    public function test_many_exception_renders_under_budget(): void
    {
        $request = Request::create('/api/v1/x', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            ApiExceptionRenderer::render($request, new \RuntimeException('x'));
        }
        $avgMs = ((microtime(true) - $start) * 1000) / 1000;

        $this->assertLessThan(30.0, $avgMs);
    }
}
