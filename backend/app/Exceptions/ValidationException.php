<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;

class ValidationException extends DomainException
{
    /**
     * @param  array<string, array<int, string>>  $errors
     */
    public function __construct(
        string $message,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return ErrorCode::VALIDATION_ERROR->value;
    }

    public function getHttpStatus(): int
    {
        return ErrorCode::VALIDATION_ERROR->httpStatus();
    }

    public function getDetails(): ?array
    {
        return $this->errors;
    }
}
