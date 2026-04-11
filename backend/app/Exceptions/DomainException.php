<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;
use Exception;

abstract class DomainException extends Exception implements ExceptionContract
{
    public function getErrorCode(): string
    {
        return ErrorCode::SERVER_ERROR->value;
    }

    public function getHttpStatus(): int
    {
        return ErrorCode::SERVER_ERROR->httpStatus();
    }

    public function getDetails(): ?array
    {
        return null;
    }
}
