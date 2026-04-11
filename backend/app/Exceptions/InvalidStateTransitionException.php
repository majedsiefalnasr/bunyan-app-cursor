<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;

class InvalidStateTransitionException extends DomainException
{
    public function __construct(
        string $message,
        public readonly string $fromState,
        public readonly string $toState,
        public readonly array $allowedTransitions = [],
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return ErrorCode::WORKFLOW_INVALID_TRANSITION->value;
    }

    public function getHttpStatus(): int
    {
        return ErrorCode::WORKFLOW_INVALID_TRANSITION->httpStatus();
    }

    public function getDetails(): ?array
    {
        return [
            'from_state' => $this->fromState,
            'to_state' => $this->toState,
            'allowed_transitions' => $this->allowedTransitions,
        ];
    }
}
