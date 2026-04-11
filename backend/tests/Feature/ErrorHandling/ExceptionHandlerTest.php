<?php

namespace Tests\Feature\ErrorHandling;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_sandbox_domain_validation(): void
    {
        $response = $this->getJson('/api/v1/__errors/domain_validation');
        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }

    public function test_sandbox_invalid_state(): void
    {
        $response = $this->getJson('/api/v1/__errors/invalid_state');
        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'WORKFLOW_INVALID_TRANSITION');
    }

    public function test_sandbox_resource_not_found(): void
    {
        $response = $this->getJson('/api/v1/__errors/resource_not_found');
        $response->assertStatus(404);
        $response->assertJsonPath('error.code', 'RESOURCE_NOT_FOUND');
    }

    public function test_sandbox_payment_failed(): void
    {
        $response = $this->getJson('/api/v1/__errors/payment_failed');
        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'PAYMENT_FAILED');
    }

    public function test_sandbox_workflow_prereq(): void
    {
        $response = $this->getJson('/api/v1/__errors/workflow_prereq');
        $response->assertStatus(422);
        $response->assertJsonPath('error.code', 'WORKFLOW_PREREQUISITES_UNMET');
    }

    public function test_sandbox_server_error_hides_trace_for_customer(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/__errors/server_error');
        $response->assertStatus(500);
        $response->assertJsonPath('error.code', 'SERVER_ERROR');
        $details = $response->json('error.details');
        $this->assertTrue($details === null || ! is_array($details) || ! array_key_exists('_debug', $details));
    }

    public function test_sandbox_server_error_includes_debug_for_admin_in_testing(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/__errors/server_error');
        $response->assertStatus(500);
        $response->assertJsonPath('error.code', 'SERVER_ERROR');
        $response->assertJsonPath('error.details._debug.exception', \RuntimeException::class);
    }

    public function test_unauthenticated_profile_returns_401(): void
    {
        $response = $this->getJson('/api/v1/auth/profile');
        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'AUTH_UNAUTHORIZED');
    }

    public function test_auth_token_expired_mapping(): void
    {
        $response = $this->getJson('/api/v1/__errors/auth_token_expired');
        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'AUTH_TOKEN_EXPIRED');
    }

    public function test_throttle_mapping(): void
    {
        $response = $this->getJson('/api/v1/__errors/throttle');
        $response->assertStatus(429);
        $response->assertJsonPath('error.code', 'RATE_LIMIT_EXCEEDED');
    }

    public function test_service_unavailable_mapping(): void
    {
        $response = $this->getJson('/api/v1/__errors/service_unavailable');
        $response->assertStatus(503);
        $response->assertJsonPath('error.code', 'SERVICE_UNAVAILABLE');
    }

    public function test_invalid_login_returns_auth_invalid_credentials(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');
    }

    public function test_model_not_found_returns_resource_not_found(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/projects/999999');
        $response->assertStatus(404);
        $response->assertJsonPath('error.code', 'RESOURCE_NOT_FOUND');
    }

    public function test_correlation_header_on_error(): void
    {
        $response = $this->getJson('/api/v1/__errors/server_error', [
            'X-Correlation-ID' => 'req_test_123',
        ]);

        $response->assertHeader('X-Correlation-ID', 'req_test_123');
    }

    public function test_rbac_role_matrix_contractor_denied(): void
    {
        $user = User::factory()->contractor()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/projects', [
            'name' => 'X',
            'budget' => 1,
            'location' => 'Y',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error.code', 'RBAC_ROLE_DENIED');
    }

    public function test_field_engineer_denied_project_create(): void
    {
        $user = User::factory()->fieldEngineer()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/projects', [
            'name' => 'X',
            'budget' => 1,
            'location' => 'Y',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error.code', 'RBAC_ROLE_DENIED');
    }

    public function test_supervising_architect_denied_project_create(): void
    {
        $user = User::factory()->supervisingArchitect()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/projects', [
            'name' => 'X',
            'budget' => 1,
            'location' => 'Y',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('error.code', 'RBAC_ROLE_DENIED');
    }
}
