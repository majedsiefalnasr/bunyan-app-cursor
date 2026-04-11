<?php

namespace Tests\Unit\Services;

use App\Enums\ErrorCode;
use App\Services\ErrorCodeRegistry;
use PHPUnit\Framework\TestCase;

class ErrorCodeRegistryTest extends TestCase
{
    public function test_get_for_all_codes(): void
    {
        $registry = new ErrorCodeRegistry;

        foreach (ErrorCode::cases() as $case) {
            $meta = $registry->get($case);
            $this->assertSame($case->httpStatus(), $meta['http_status']);
            $this->assertSame($case->severity(), $meta['severity']);
            $this->assertSame($case->description(), $meta['description']);
            $this->assertSame($registry->isRetryable($case), $meta['retryable']);
        }
    }

    public function test_http_status_helper(): void
    {
        $registry = new ErrorCodeRegistry;
        $this->assertSame(429, $registry->httpStatus(ErrorCode::RATE_LIMIT_EXCEEDED));
    }

    public function test_all_contains_every_code(): void
    {
        $registry = new ErrorCodeRegistry;
        $all = $registry->all();
        foreach (ErrorCode::cases() as $case) {
            $this->assertArrayHasKey($case->value, $all);
        }
    }
}
