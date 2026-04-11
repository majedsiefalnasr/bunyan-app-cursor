<?php

namespace Tests\Feature\ErrorHandling;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2EErrorRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_retry_after_validation_error(): void
    {
        $user = User::factory()->customer()->create();

        $first = $this->actingAs($user)->postJson('/api/v1/projects', [
            'name' => 'Retry',
            'budget' => -1,
            'location' => 'Riyadh',
        ]);
        $first->assertStatus(422);

        $second = $this->actingAs($user)->postJson('/api/v1/projects', [
            'name' => 'Retry',
            'budget' => 10,
            'location' => 'Riyadh',
        ]);
        $second->assertStatus(201);
    }
}
