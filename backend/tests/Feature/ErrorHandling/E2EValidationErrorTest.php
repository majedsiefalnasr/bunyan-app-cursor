<?php

namespace Tests\Feature\ErrorHandling;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class E2EValidationErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_error_contract_then_success(): void
    {
        $user = User::factory()->customer()->create();

        $bad = $this->actingAs($user)->postJson('/api/v1/projects', [
            'name' => 'OK',
            'budget' => -5,
            'location' => 'Riyadh',
        ]);
        $bad->assertStatus(422);
        $bad->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $good = $this->actingAs($user)->postJson('/api/v1/projects', [
            'name' => 'OK',
            'budget' => 100,
            'location' => 'Riyadh',
        ]);
        $good->assertStatus(201);
        $good->assertJsonPath('success', true);
    }
}
