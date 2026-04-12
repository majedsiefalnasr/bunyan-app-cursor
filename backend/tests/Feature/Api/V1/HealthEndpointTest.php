<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class HealthEndpointTest extends TestCase
{
    public function test_health_returns_success_envelope(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.service', 'bunyan-api')
            ->assertJsonPath('data.version', 'v1')
            ->assertJsonPath('error', null)
            ->assertJsonStructure([
                'success',
                'data' => ['service', 'version', 'time', 'app', 'correlation_id'],
                'message',
                'errors',
                'error',
            ]);
    }

    public function test_health_echoes_correlation_id_header(): void
    {
        $response = $this->withHeader('X-Correlation-ID', 'test-correlation-99')
            ->getJson('/api/v1/health');

        $response->assertOk()
            ->assertJsonPath('data.correlation_id', 'test-correlation-99');
    }
}
