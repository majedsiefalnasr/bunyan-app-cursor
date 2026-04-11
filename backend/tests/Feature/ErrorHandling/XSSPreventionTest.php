<?php

namespace Tests\Feature\ErrorHandling;

use App\Exceptions\ApiExceptionRenderer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class XSSPreventionTest extends TestCase
{
    use RefreshDatabase;

    public function test_validation_details_are_json_encoded(): void
    {
        $payload = '<script>alert(1)</script>';
        $request = Request::create('/api/v1/projects', 'POST', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        $e = ValidationException::withMessages(['field' => [$payload]]);
        $response = ApiExceptionRenderer::render($request, $e);
        $this->assertNotNull($response);
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertStringContainsString('VALIDATION_ERROR', (string) $response->getContent());
    }
}
