<?php

namespace Tests\Unit\Enums;

use App\Enums\ErrorCode;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ErrorCodeTest extends TestCase
{
    public function test_all_cases_have_distinct_values(): void
    {
        $values = array_map(static fn (ErrorCode $c) => $c->value, ErrorCode::cases());
        $this->assertSame(count($values), count(array_unique($values)));
    }

    #[DataProvider('statusProvider')]
    public function test_http_status_mapping(ErrorCode $code, int $expected): void
    {
        $this->assertSame($expected, $code->httpStatus());
    }

    /**
     * @return array<string, array{ErrorCode, int}>
     */
    public static function statusProvider(): array
    {
        return [
            'validation' => [ErrorCode::VALIDATION_ERROR, 422],
            'auth_invalid' => [ErrorCode::AUTH_INVALID_CREDENTIALS, 401],
            'auth_token' => [ErrorCode::AUTH_TOKEN_EXPIRED, 401],
            'auth_unauth' => [ErrorCode::AUTH_UNAUTHORIZED, 401],
            'rbac' => [ErrorCode::RBAC_ROLE_DENIED, 403],
            'not_found' => [ErrorCode::RESOURCE_NOT_FOUND, 404],
            'workflow' => [ErrorCode::WORKFLOW_INVALID_TRANSITION, 422],
            'prereq' => [ErrorCode::WORKFLOW_PREREQUISITES_UNMET, 422],
            'payment' => [ErrorCode::PAYMENT_FAILED, 422],
            'rate' => [ErrorCode::RATE_LIMIT_EXCEEDED, 429],
            'server' => [ErrorCode::SERVER_ERROR, 500],
            'service' => [ErrorCode::SERVICE_UNAVAILABLE, 503],
        ];
    }

    public function test_severity_is_non_empty(): void
    {
        foreach (ErrorCode::cases() as $case) {
            $this->assertNotSame('', $case->severity());
        }
    }

    public function test_description_is_non_empty(): void
    {
        foreach (ErrorCode::cases() as $case) {
            $this->assertNotSame('', $case->description());
        }
    }
}
