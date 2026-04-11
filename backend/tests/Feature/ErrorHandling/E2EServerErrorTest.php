<?php

namespace Tests\Feature\ErrorHandling;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2EServerErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_server_error_contract(): void
    {
        $response = $this->getJson('/api/v1/__errors/server_error');

        $response->assertStatus(500);
        $response->assertJsonPath('error.code', 'SERVER_ERROR');
    }
}
