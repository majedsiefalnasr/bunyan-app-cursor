<?php

namespace Tests\Feature\ErrorHandling;

use App\Exceptions\ApiExceptionRenderer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PerformanceBenchmarkTest extends TestCase
{
    public function test_validation_exception_rendering_is_fast(): void
    {
        $request = Request::create('/api/v1/projects', 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $e = ValidationException::withMessages(['x' => ['bad']]);

        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            ApiExceptionRenderer::render($request, $e);
        }
        $avgMs = ((microtime(true) - $start) * 1000) / 1000;

        $this->assertLessThan(30.0, $avgMs);
    }
}
