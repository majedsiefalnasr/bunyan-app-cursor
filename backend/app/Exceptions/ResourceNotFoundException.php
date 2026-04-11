<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;

class ResourceNotFoundException extends DomainException
{
    public function __construct(
        string $message,
        public readonly string $resourceType,
        public readonly mixed $resourceId,
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return ErrorCode::RESOURCE_NOT_FOUND->value;
    }

    public function getHttpStatus(): int
    {
        return ErrorCode::RESOURCE_NOT_FOUND->httpStatus();
    }

    public function getDetails(): ?array
    {
        return [
            'resource' => $this->resourceType,
            'id' => $this->resourceId,
        ];
    }
}
