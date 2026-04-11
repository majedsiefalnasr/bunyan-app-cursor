<?php

return [
    'codes' => [
        'VALIDATION_ERROR' => [
            'message' => 'The submitted data is invalid.',
        ],
        'AUTH_INVALID_CREDENTIALS' => [
            'message' => 'Invalid login credentials.',
        ],
        'AUTH_TOKEN_EXPIRED' => [
            'message' => 'Your session has expired.',
        ],
        'AUTH_UNAUTHORIZED' => [
            'message' => 'You must sign in first.',
        ],
        'RBAC_ROLE_DENIED' => [
            'message' => 'You are not allowed to perform this action.',
        ],
        'RESOURCE_NOT_FOUND' => [
            'message' => 'The requested resource was not found.',
        ],
        'WORKFLOW_INVALID_TRANSITION' => [
            'message' => 'This status transition is not allowed.',
        ],
        'WORKFLOW_PREREQUISITES_UNMET' => [
            'message' => 'Prerequisites are not satisfied.',
        ],
        'PAYMENT_FAILED' => [
            'message' => 'Payment processing failed.',
        ],
        'RATE_LIMIT_EXCEEDED' => [
            'message' => 'Too many requests.',
        ],
        'SERVER_ERROR' => [
            'message' => 'An unexpected error occurred.',
        ],
        'SERVICE_UNAVAILABLE' => [
            'message' => 'The service is temporarily unavailable.',
        ],
    ],
];
