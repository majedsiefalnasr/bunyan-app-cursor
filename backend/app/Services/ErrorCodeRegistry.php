<?php

namespace App\Services;

use App\Enums\ErrorCode;

class ErrorCodeRegistry
{
    /**
     * @return array{http_status: int, severity: string, description: string, retryable: bool}
     */
    public function get(ErrorCode|string $code): array
    {
        $enum = $code instanceof ErrorCode ? $code : ErrorCode::from((string) $code);

        return [
            'http_status' => $enum->httpStatus(),
            'severity' => $enum->severity(),
            'description' => $enum->description(),
            'retryable' => $this->isRetryable($enum),
        ];
    }

    public function httpStatus(ErrorCode|string $code): int
    {
        return $this->get($code)['http_status'];
    }

    public function severity(ErrorCode|string $code): string
    {
        return $this->get($code)['severity'];
    }

    public function isRetryable(ErrorCode|string $code): bool
    {
        $enum = $code instanceof ErrorCode ? $code : ErrorCode::from((string) $code);

        return match ($enum) {
            ErrorCode::RATE_LIMIT_EXCEEDED,
            ErrorCode::SERVICE_UNAVAILABLE,
            ErrorCode::SERVER_ERROR => true,
            default => false,
        };
    }

    /**
     * @return array<string, array{http_status: int, severity: string, description: string, retryable: bool}>
     */
    public function all(): array
    {
        $out = [];
        foreach (ErrorCode::cases() as $case) {
            $out[$case->value] = $this->get($case);
        }

        return $out;
    }
}
