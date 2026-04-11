<?php

namespace App\Exceptions;

use App\Enums\ErrorCode;

class PaymentFailedException extends DomainException
{
    public function __construct(
        string $message,
        public readonly ?string $transactionId = null,
        public readonly ?string $reason = null,
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return ErrorCode::PAYMENT_FAILED->value;
    }

    public function getHttpStatus(): int
    {
        return ErrorCode::PAYMENT_FAILED->httpStatus();
    }

    public function getDetails(): ?array
    {
        $details = array_filter([
            'transaction_id' => $this->transactionId,
            'reason' => $this->reason,
        ], static fn ($v) => $v !== null);

        return $details === [] ? null : $details;
    }
}
