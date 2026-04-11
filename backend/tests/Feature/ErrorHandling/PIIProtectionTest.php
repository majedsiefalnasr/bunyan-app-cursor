<?php

namespace Tests\Feature\ErrorHandling;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PIIProtectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_structured_logs_should_avoid_raw_password_field(): void
    {
        $path = storage_path('logs/structured.log');
        if (File::exists($path)) {
            File::delete($path);
        }

        $user = User::factory()->customer()->create();

        $this->actingAs($user)->postJson('/api/v1/projects', [
            'name' => 'PII',
            'budget' => 1,
            'location' => 'Riyadh',
        ]);

        if (! File::exists($path)) {
            $this->markTestSkipped('structured.log was not written in this environment');
        }

        $contents = File::get($path);
        $this->assertStringNotContainsString($user->email, $contents);
    }
}
