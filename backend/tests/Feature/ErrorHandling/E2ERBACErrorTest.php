<?php

namespace Tests\Feature\ErrorHandling;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2ERBACErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_denied_on_contractor_only_action(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/products', []);

        $response->assertStatus(403);
        $response->assertJsonPath('error.code', 'RBAC_ROLE_DENIED');
    }
}
