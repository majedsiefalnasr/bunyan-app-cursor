<?php

namespace Tests\Feature\ErrorHandling;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorrelationIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_propagates_request_header(): void
    {
        $response = $this->getJson('/api/v1/__errors/server_error', [
            'X-Correlation-ID' => 'req_propagate_1',
        ]);

        $response->assertHeader('X-Correlation-ID', 'req_propagate_1');
    }

    public function test_generates_when_missing(): void
    {
        $response = $this->getJson('/api/v1/__errors/server_error');

        $this->assertMatchesRegularExpression('/^req_\d+_[0-9a-f]{8}$/', (string) $response->headers->get('X-Correlation-ID'));
    }

    public function test_authenticated_error_still_has_correlation(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/__errors/server_error', [
            'X-Correlation-ID' => 'req_auth_1',
        ]);

        $response->assertHeader('X-Correlation-ID', 'req_auth_1');
    }
}
