<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;

class WorkflowPrerequisiteException extends DomainException
{
    /**
     * @param  array<string, mixed>  $details
     */
    public function __construct(
        string $message,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return ErrorCode::WORKFLOW_PREREQUISITES_UNMET->value;
    }

    public function getHttpStatus(): int
    {
        return ErrorCode::WORKFLOW_PREREQUISITES_UNMET->httpStatus();
    }

    public function getDetails(): ?array
    {
        return $this->details === [] ? null : $this->details;
    }
}
