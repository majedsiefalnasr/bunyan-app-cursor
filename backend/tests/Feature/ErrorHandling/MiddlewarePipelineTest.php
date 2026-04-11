<?php

namespace Tests\Feature\ErrorHandling;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewarePipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_correlation_id_middleware_runs_for_api_requests(): void
    {
        $response = $this->getJson('/api/v1/__errors/auth_unauthorized');

        $response->assertStatus(401);
        $this->assertNotNull($response->headers->get('X-Correlation-ID'));
    }
}
