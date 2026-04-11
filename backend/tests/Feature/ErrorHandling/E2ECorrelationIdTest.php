<?php

namespace Tests\Feature\ErrorHandling;

use App\Services\ErrorLoggingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2ECorrelationIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_correlation_id_is_stable_on_error_response(): void
    {
        $response = $this->getJson('/api/v1/__errors/server_error', [
            'X-Correlation-ID' => 'req_e2e_corr',
        ]);

        $response->assertHeader('X-Correlation-ID', 'req_e2e_corr');

        $service = app(ErrorLoggingService::class);
        $service->log([
            'correlation_id' => 'req_e2e_corr',
            'error_code' => 'SERVER_ERROR',
            'message' => 'test',
            'details' => null,
            'context' => [],
            'severity' => 'error',
            'http_status' => 500,
            'exception_class' => null,
            'stack_trace' => null,
            'user_id' => null,
            'user_role' => null,
            'request_method' => 'GET',
            'request_path' => '/api/v1/__errors/server_error',
            'request_ip' => '127.0.0.1',
            'response_time_ms' => 1,
        ]);

        $this->assertGreaterThanOrEqual(1, $service->findByCorrelationId('req_e2e_corr')->count());
    }
}
