<?php

namespace Tests\Feature\ErrorHandling;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ErrorContractComplianceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, array{string}>
     */
    public static function unauthenticatedEndpoints(): array
    {
        return [
            ['/api/v1/projects'],
            ['/api/v1/products'],
            ['/api/v1/reports'],
            ['/api/v1/transactions'],
            ['/api/v1/orders'],
        ];
    }

    #[DataProvider('unauthenticatedEndpoints')]
    public function test_unauthenticated_errors_follow_contract(string $path): void
    {
        $response = $this->getJson($path);

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('data', null);
        $response->assertJsonStructure(['error' => ['code', 'message', 'details']]);
    }
}
