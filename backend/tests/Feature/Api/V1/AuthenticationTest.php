<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    // ── Register ─────────────────────────────────────────────────────

    public function test_user_can_register(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'أحمد محمد',
            'email' => 'ahmed@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'phone' => '+966501234567',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email', 'role', 'phone', 'active'],
                    'token',
                ],
                'message',
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.role', 'customer')
            ->assertJsonPath('data.user.email', 'ahmed@example.com')
            ->assertJsonPath('data.user.name', 'أحمد محمد');

        $this->assertDatabaseHas('users', [
            'email' => 'ahmed@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_register_validates_weak_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_validates_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'DifferentPassword1',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_requires_name_and_email(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
        ]);

        $response->assertStatus(422);
    }

    public function test_register_rejects_role_field(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Sneaky Admin',
            'email' => 'sneaky@example.com',
            'password' => 'Password1',
            'password_confirmation' => 'Password1',
            'role' => 'admin',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'VALIDATION_ERROR');

        $details = $response->json('error.details');
        $this->assertIsArray($details);
        $this->assertArrayHasKey('role', $details);

        $this->assertDatabaseMissing('users', [
            'email' => 'sneaky@example.com',
        ]);
    }

    // ── Login ────────────────────────────────────────────────────────

    public function test_user_can_login(): void
    {
        User::factory()->create([
            'email' => 'ahmed@example.com',
            'password' => 'Password1',
            'active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ahmed@example.com',
            'password' => 'Password1',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['user', 'token'],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'ahmed@example.com');
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'ahmed@example.com',
            'password' => 'Password1',
            'active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ahmed@example.com',
            'password' => 'WrongPassword1',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');
    }

    public function test_login_fails_for_inactive_user(): void
    {
        User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
            'password' => 'Password1',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@example.com',
            'password' => 'Password1',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'AUTH_ACCOUNT_INACTIVE');
    }

    public function test_login_fails_for_nonexistent_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'ghost@example.com',
            'password' => 'Password1',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('error.code', 'AUTH_INVALID_CREDENTIALS');
    }

    public function test_login_validates_email_format(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'not-an-email',
            'password' => 'Password1',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422);
    }

    // ── Logout ───────────────────────────────────────────────────────

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(401);
    }

    // ── Profile ──────────────────────────────────────────────────────

    public function test_user_can_get_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'أحمد',
            'email' => 'ahmed@example.com',
        ]);
        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->getJson('/api/v1/auth/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'name', 'email', 'role', 'phone', 'active', 'email_verified_at'],
            ])
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.email', 'ahmed@example.com')
            ->assertJsonPath('data.name', 'أحمد');
    }

    public function test_profile_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/auth/profile');

        $response->assertStatus(401);
    }

    // ── Update Profile ───────────────────────────────────────────────

    public function test_user_can_update_name(): void
    {
        $user = User::factory()->create(['name' => 'Old Name']);
        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson('/api/v1/auth/profile', [
            'name' => 'محمد أحمد',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'محمد أحمد');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'محمد أحمد',
        ]);
    }

    public function test_user_can_update_phone(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson('/api/v1/auth/profile', [
            'phone' => '+966509876543',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.phone', '+966509876543');
    }

    public function test_update_profile_requires_authentication(): void
    {
        $response = $this->putJson('/api/v1/auth/profile', [
            'name' => 'Hacker',
        ]);

        $response->assertStatus(401);
    }

    // ── Forgot Password ──────────────────────────────────────────────

    public function test_forgot_password_returns_success_for_existing_email(): void
    {
        Notification::fake();

        User::factory()->create(['email' => 'forgot@example.com']);

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'forgot@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_forgot_password_returns_success_for_nonexistent_email(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_forgot_password_validates_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
    }

    public function test_forgot_password_requires_email(): void
    {
        $response = $this->postJson('/api/v1/auth/forgot-password', []);

        $response->assertStatus(422);
    }

    // ── Reset Password ───────────────────────────────────────────────

    public function test_reset_password_success(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $token = Password::broker()->createToken($user);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'reset@example.com',
            'token' => $token,
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertTrue(Hash::check('NewPassword1', $user->fresh()->password));
    }

    public function test_reset_password_fails_with_invalid_token(): void
    {
        User::factory()->create(['email' => 'reset@example.com']);

        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'reset@example.com',
            'token' => 'invalid-token',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'AUTH_INVALID_RESET_TOKEN');
    }

    public function test_reset_password_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/reset-password', []);

        $response->assertStatus(422);
    }

    // ── Email Verification ───────────────────────────────────────────

    public function test_resend_verification_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/auth/email/resend');

        $response->assertStatus(401);
    }

    public function test_resend_verification_for_unverified_user(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => null]);
        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/v1/auth/email/resend');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_resend_verification_for_already_verified_user(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/v1/auth/email/resend');

        $response->assertStatus(422)
            ->assertJsonPath('error.code', 'AUTH_EMAIL_ALREADY_VERIFIED');
    }
}
