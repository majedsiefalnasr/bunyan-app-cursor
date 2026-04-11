<?php

namespace Tests\Feature\ErrorHandling;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorDetailFilteringTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_never_receives_debug_details(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/__errors/server_error');

        $response->assertStatus(500);
        $details = $response->json('error.details');
        $this->assertTrue($details === null || ! is_array($details) || ! array_key_exists('_debug', $details));
    }

    public function test_admin_receives_debug_in_testing(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/__errors/server_error');

        $response->assertStatus(500);
        $response->assertJsonPath('error.details._debug.exception', \RuntimeException::class);
    }
}
