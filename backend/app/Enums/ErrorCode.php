<?php

namespace App\Enums;

enum ErrorCode: string
{
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case AUTH_INVALID_CREDENTIALS = 'AUTH_INVALID_CREDENTIALS';
    case AUTH_TOKEN_EXPIRED = 'AUTH_TOKEN_EXPIRED';
    case AUTH_UNAUTHORIZED = 'AUTH_UNAUTHORIZED';
    case RBAC_ROLE_DENIED = 'RBAC_ROLE_DENIED';
    case RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    case WORKFLOW_INVALID_TRANSITION = 'WORKFLOW_INVALID_TRANSITION';
    case WORKFLOW_PREREQUISITES_UNMET = 'WORKFLOW_PREREQUISITES_UNMET';
    case PAYMENT_FAILED = 'PAYMENT_FAILED';
    case AUTH_ACCOUNT_INACTIVE = 'AUTH_ACCOUNT_INACTIVE';
    case AUTH_INVALID_RESET_TOKEN = 'AUTH_INVALID_RESET_TOKEN';
    case AUTH_EMAIL_ALREADY_VERIFIED = 'AUTH_EMAIL_ALREADY_VERIFIED';
    case AUTH_EMAIL_NOT_VERIFIED = 'AUTH_EMAIL_NOT_VERIFIED';
    case RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED';
    case SERVER_ERROR = 'SERVER_ERROR';
    case SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';

    public function httpStatus(): int
    {
        return match ($this) {
            self::VALIDATION_ERROR,
            self::WORKFLOW_INVALID_TRANSITION,
            self::WORKFLOW_PREREQUISITES_UNMET,
            self::PAYMENT_FAILED,
            self::AUTH_INVALID_RESET_TOKEN,
            self::AUTH_EMAIL_ALREADY_VERIFIED,
            self::AUTH_EMAIL_NOT_VERIFIED => 422,
            self::AUTH_INVALID_CREDENTIALS,
            self::AUTH_TOKEN_EXPIRED,
            self::AUTH_UNAUTHORIZED => 401,
            self::RBAC_ROLE_DENIED,
            self::AUTH_ACCOUNT_INACTIVE => 403,
            self::RESOURCE_NOT_FOUND => 404,
            self::RATE_LIMIT_EXCEEDED => 429,
            self::SERVER_ERROR => 500,
            self::SERVICE_UNAVAILABLE => 503,
        };
    }

    public function severity(): string
    {
        return match ($this) {
            self::SERVER_ERROR,
            self::SERVICE_UNAVAILABLE,
            self::PAYMENT_FAILED => 'error',
            default => 'warning',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::VALIDATION_ERROR => 'Input validation failed',
            self::AUTH_INVALID_CREDENTIALS => 'Login credentials incorrect',
            self::AUTH_TOKEN_EXPIRED => 'Authentication token expired',
            self::AUTH_UNAUTHORIZED => 'User not authenticated',
            self::AUTH_ACCOUNT_INACTIVE => 'User account is deactivated',
            self::AUTH_INVALID_RESET_TOKEN => 'Password reset token is invalid',
            self::AUTH_EMAIL_ALREADY_VERIFIED => 'Email is already verified',
            self::AUTH_EMAIL_NOT_VERIFIED => 'Email is not verified',
            self::RBAC_ROLE_DENIED => 'User role not permitted',
            self::RESOURCE_NOT_FOUND => 'Requested resource not found',
            self::WORKFLOW_INVALID_TRANSITION => 'Invalid state transition',
            self::WORKFLOW_PREREQUISITES_UNMET => 'Prerequisites not satisfied',
            self::PAYMENT_FAILED => 'Payment processing failed',
            self::RATE_LIMIT_EXCEEDED => 'Too many requests',
            self::SERVER_ERROR => 'Internal server error',
            self::SERVICE_UNAVAILABLE => 'Service temporarily unavailable',
        };
    }
}
