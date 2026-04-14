<?php

namespace Tests\Unit\Services;

use App\Services\LoggingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class LoggingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_request_response_error_and_activity_with_correlation_id(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->with('request', \Mockery::on(fn ($c) => ($c['correlation_id'] ?? null) === 'corr-1'));

        Log::shouldReceive('info')
            ->once()
            ->with('response', \Mockery::on(fn ($c) => ($c['response_status'] ?? null) === 201));

        Log::shouldReceive('error')
            ->once()
            ->with('error', \Mockery::on(fn ($c) => ($c['exception'] ?? null) === \RuntimeException::class));

        Log::shouldReceive('info')
            ->once()
            ->with('custom.action', \Mockery::on(fn ($c) => ($c['z'] ?? null) === 3));

        $request = Request::create('/api/v1/test', 'GET');
        $request->attributes->set('correlation_id', 'corr-1');

        $service = new LoggingService;

        $service->logRequest($request, ['k' => 'v']);
        $service->logResponse($request, 201, 12.5, ['x' => 1]);
        $service->logError($request, new \RuntimeException('boom'), ['y' => 2]);
        $service->logActivity($request, 'custom.action', ['z' => 3]);
    }
}
