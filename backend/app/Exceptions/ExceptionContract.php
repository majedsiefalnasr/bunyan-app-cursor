<?php

namespace App\Exceptions;

interface ExceptionContract
{
    public function getErrorCode(): string;

    public function getHttpStatus(): int;

    public function getDetails(): ?array;
}
