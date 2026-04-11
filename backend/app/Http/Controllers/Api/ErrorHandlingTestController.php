<?php

namespace App\Http\Controllers\Api;

use App\Enums\ErrorCode;
use App\Exceptions\InvalidStateTransitionException;
use App\Exceptions\PaymentFailedException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationException as AppValidationException;
use App\Exceptions\WorkflowPrerequisiteException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ErrorHandlingTestController
{
    public function show(Request $request, string $type): mixed
    {
        return match ($type) {
            'domain_validation' => throw new AppValidationException('validation', ['field' => ['msg']]),
            'invalid_state' => throw new InvalidStateTransitionException('bad', 'a', 'b', ['c']),
            'resource_not_found' => throw new ResourceNotFoundException('missing', 'Project', '123'),
            'payment_failed' => throw new PaymentFailedException('pay', 't1', 'declined'),
            'auth_unauthorized' => throw new AuthenticationException,
            'auth_token_expired' => throw new AuthenticationException('Token has expired'),
            'workflow_prereq' => throw new WorkflowPrerequisiteException(
                __('errors.codes.'.ErrorCode::WORKFLOW_PREREQUISITES_UNMET->value.'.message', [], 'ar'),
                ['gate' => ['blocked']],
            ),
            'server_error' => throw new \RuntimeException('boom'),
            'service_unavailable' => throw new HttpException(503, 'down'),
            'throttle' => throw new ThrottleRequestsException('Too Many Attempts.', null, ['Retry-After' => 60]),
            default => abort(404),
        };
    }
}
