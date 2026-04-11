<?php

namespace Tests\Feature\ErrorHandling;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class SecurityLogsTest extends TestCase
{
    use RefreshDatabase;

    public function test_structured_logs_do_not_contain_password_patterns(): void
    {
        $path = storage_path('logs/structured.log');
        if (File::exists($path)) {
            File::delete($path);
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'SuperSecretPassword123!',
        ]);

        if (! File::exists($path)) {
            $this->markTestSkipped('structured.log was not written in this environment');
        }

        $contents = File::get($path);
        $this->assertStringNotContainsString('SuperSecretPassword123!', $contents);
        $this->assertDoesNotMatchRegularExpression('/\"password\"\\s*:\\s*\"[^\"]+\"/i', $contents);
    }
}
