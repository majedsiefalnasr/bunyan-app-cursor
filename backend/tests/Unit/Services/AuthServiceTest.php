<?php

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_user_and_token_when_credentials_valid(): void
    {
        $user = User::factory()->create(['email' => 'auth@example.com']);

        $service = new AuthService;
        $result = $service->login('auth@example.com', 'password');

        $this->assertNotNull($result);
        $this->assertSame($user->id, $result['user']->id);
        $this->assertNotEmpty($result['token']);
    }

    public function test_login_returns_null_when_email_unknown(): void
    {
        $service = new AuthService;

        $this->assertNull($service->login('missing@example.com', 'password'));
    }

    public function test_login_returns_null_when_password_invalid(): void
    {
        User::factory()->create(['email' => 'auth@example.com']);

        $service = new AuthService;

        $this->assertNull($service->login('auth@example.com', 'wrong-password'));
    }

    public function test_register_creates_user_and_returns_token(): void
    {
        $service = new AuthService;

        $result = $service->register([
            'name' => 'Registered',
            'email' => 'new@example.com',
            'password' => 'Secret123!',
            'role' => UserRole::Customer->value,
            'phone' => '+966500000000',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $this->assertSame('Registered', $result['user']->name);
        $this->assertNotEmpty($result['token']);
    }

    public function test_logout_revokes_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('api_token');

        $service = new AuthService;
        $service->logout($user);

        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_refresh_token_replaces_existing_tokens(): void
    {
        $user = User::factory()->create();
        $old = $user->createToken('api_token')->plainTextToken;

        $service = new AuthService;
        $new = $service->refreshToken($user);

        $this->assertNotSame($old, $new);
        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_validate_token_returns_null_when_unauthenticated(): void
    {
        $service = new AuthService;

        $this->assertNull($service->validateToken('any'));
    }

    public function test_validate_token_returns_user_when_sanctum_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $service = new AuthService;

        $this->assertSame($user->id, $service->validateToken('any')->id);
    }
}
