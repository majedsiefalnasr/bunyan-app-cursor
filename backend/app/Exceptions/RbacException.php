<?php

namespace App\Exceptions;

use RuntimeException;

class RbacException extends RuntimeException
{
    public function __construct(
        string $message,
        private string $errorCode = 'RBAC_ROLE_DENIED',
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
