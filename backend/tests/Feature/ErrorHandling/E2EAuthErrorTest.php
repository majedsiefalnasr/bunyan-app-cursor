<?php

namespace Tests\Feature\ErrorHandling;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2EAuthErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_api_request_returns_contract(): void
    {
        $response = $this->getJson('/api/v1/auth/profile');

        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'AUTH_UNAUTHORIZED');
    }
}
