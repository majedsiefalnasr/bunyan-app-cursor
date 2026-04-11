<?php

namespace Tests\Feature\ErrorHandling;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidationErrorResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_missing_required_fields_returns_contract_and_arabic_message(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/projects', [], [
            'Accept-Language' => 'ar',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('data', null);
        $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
        $response->assertJsonStructure(['error' => ['code', 'message', 'details']]);
        $this->assertNotSame('', (string) $response->json('error.message'));
    }

    public function test_invalid_budget_type_returns_validation_error(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/projects', [
            'name' => 'Test',
            'budget' => 'not-a-number',
            'location' => 'Riyadh',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_contractor_cannot_create_project_returns_403(): void
    {
        $user = User::factory()->contractor()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/projects', [
            'name' => 'Test',
            'budget' => 100,
            'location' => 'Riyadh',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error.code', 'RBAC_ROLE_DENIED');
    }

    public function test_register_validation_returns_contract(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_update_profile_validation_contract(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->putJson('/api/v1/auth/profile', [
            'name' => str_repeat('a', 300),
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }
}
