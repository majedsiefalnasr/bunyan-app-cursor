<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    // ── Login ────────────────────────────────────────────────────────

    public function test_login_success(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password1',
            'active' => true,
        ]);

        $result = $this->authService->login('test@example.com', 'Password1');

        $this->assertEquals('success', $result['status']);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals($user->id, $result['user']->id);
        $this->assertNotEmpty($result['token']);
    }

    public function test_login_invalid_credentials_wrong_password(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'Password1',
            'active' => true,
        ]);

        $result = $this->authService->login('test@example.com', 'WrongPassword1');

        $this->assertEquals('invalid_credentials', $result['status']);
        $this->assertArrayNotHasKey('user', $result);
        $this->assertArrayNotHasKey('token', $result);
    }

    public function test_login_invalid_credentials_nonexistent_email(): void
    {
        $result = $this->authService->login('nonexistent@example.com', 'Password1');

        $this->assertEquals('invalid_credentials', $result['status']);
    }

    public function test_login_inactive_account(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => 'Password1',
            'active' => false,
        ]);

        $result = $this->authService->login('inactive@example.com', 'Password1');

        $this->assertEquals('account_inactive', $result['status']);
        $this->assertArrayNotHasKey('token', $result);
    }

    // ── Register ─────────────────────────────────────────────────────

    public function test_register_success(): void
    {
        Notification::fake();

        $result = $this->authService->register([
            'name' => 'أحمد محمد',
            'email' => 'ahmed@example.com',
            'password' => 'Password1',
            'phone' => '+966501234567',
        ]);

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals('ahmed@example.com', $result['user']->email);
        $this->assertEquals('أحمد محمد', $result['user']->name);
        $this->assertNotEmpty($result['token']);
        $this->assertDatabaseHas('users', ['email' => 'ahmed@example.com']);
    }

    public function test_register_forces_customer_role(): void
    {
        Notification::fake();

        $result = $this->authService->register([
            'name' => 'Sneaky Admin',
            'email' => 'sneaky@example.com',
            'password' => 'Password1',
            'role' => 'admin',
        ]);

        $this->assertEquals('customer', $result['user']->role->value);
        $this->assertDatabaseHas('users', [
            'email' => 'sneaky@example.com',
            'role' => 'customer',
        ]);
    }

    public function test_register_sets_user_active(): void
    {
        Notification::fake();

        $result = $this->authService->register([
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'Password1',
        ]);

        $this->assertTrue($result['user']->active);
    }

    public function test_register_hashes_password(): void
    {
        Notification::fake();

        $result = $this->authService->register([
            'name' => 'New User',
            'email' => 'hash@example.com',
            'password' => 'Password1',
        ]);

        $user = $result['user']->fresh();
        $this->assertTrue(Hash::check('Password1', $user->password));
        $this->assertNotEquals('Password1', $user->password);
    }

    // ── Logout ───────────────────────────────────────────────────────

    public function test_logout_revokes_all_tokens(): void
    {
        $user = User::factory()->create();
        $user->createToken('token1');
        $user->createToken('token2');

        $this->assertEquals(2, $user->tokens()->count());

        $this->authService->logout($user);

        $this->assertEquals(0, $user->tokens()->count());
    }

    // ── Refresh Token ────────────────────────────────────────────────

    public function test_refresh_token_replaces_existing_tokens(): void
    {
        $user = User::factory()->create();
        $old = $user->createToken('api_token')->plainTextToken;

        $new = $this->authService->refreshToken($user);

        $this->assertNotEquals($old, $new);
        $this->assertNotEmpty($new);
        $this->assertEquals(1, $user->tokens()->count());
    }

    // ── Validate Token ───────────────────────────────────────────────

    public function test_validate_token_returns_null_when_unauthenticated(): void
    {
        $this->assertNull($this->authService->validateToken('any'));
    }

    public function test_validate_token_returns_user_when_authenticated(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $result = $this->authService->validateToken('any');

        $this->assertNotNull($result);
        $this->assertEquals($user->id, $result->id);
    }

    // ── Forgot Password ──────────────────────────────────────────────

    public function test_forgot_password_sends_reset_link(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'forgot@example.com']);

        $this->authService->forgotPassword('forgot@example.com');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_does_not_throw_for_nonexistent_email(): void
    {
        Notification::fake();

        $this->authService->forgotPassword('ghost@example.com');

        Notification::assertNothingSent();
    }

    // ── Reset Password ───────────────────────────────────────────────

    public function test_reset_password_success(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $token = Password::broker()->createToken($user);

        $status = $this->authService->resetPassword([
            'email' => 'reset@example.com',
            'token' => $token,
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

        $this->assertEquals(Password::PASSWORD_RESET, $status);
        $this->assertTrue(Hash::check('NewPassword1', $user->fresh()->password));
    }

    public function test_reset_password_revokes_tokens(): void
    {
        $user = User::factory()->create(['email' => 'reset@example.com']);
        $user->createToken('old_token');
        $token = Password::broker()->createToken($user);

        $this->assertEquals(1, $user->tokens()->count());

        $this->authService->resetPassword([
            'email' => 'reset@example.com',
            'token' => $token,
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

        $this->assertEquals(0, $user->tokens()->count());
    }

    public function test_reset_password_invalid_token(): void
    {
        User::factory()->create(['email' => 'reset@example.com']);

        $status = $this->authService->resetPassword([
            'email' => 'reset@example.com',
            'token' => 'invalid-token',
            'password' => 'NewPassword1',
            'password_confirmation' => 'NewPassword1',
        ]);

        $this->assertNotEquals(Password::PASSWORD_RESET, $status);
    }

    // ── Verify Email ─────────────────────────────────────────────────

    public function test_verify_email_success(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $result = $this->authService->verifyEmail($user);

        $this->assertTrue($result);
        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_verify_email_already_verified(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $result = $this->authService->verifyEmail($user);

        $this->assertFalse($result);
    }

    // ── Resend Verification ──────────────────────────────────────────

    public function test_resend_verification_success(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => null]);

        $result = $this->authService->resendVerification($user);

        $this->assertTrue($result);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_resend_verification_already_verified(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);

        $result = $this->authService->resendVerification($user);

        $this->assertFalse($result);
        Notification::assertNothingSent();
    }
}
