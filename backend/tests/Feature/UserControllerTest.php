<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_user_successfully()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'phone' => '+966501234567',
        ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user.email', 'test@example.com');
    }

    public function test_register_with_invalid_email()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_login_user_successfully()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.user.email', 'test@example.com');
    }

    public function test_login_with_wrong_password()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_logout_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
