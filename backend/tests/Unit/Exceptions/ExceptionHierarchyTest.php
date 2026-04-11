<?php

namespace Tests\Unit\Exceptions;

use App\Exceptions\ExceptionContract;
use App\Exceptions\InvalidStateTransitionException;
use App\Exceptions\PaymentFailedException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationException;
use PHPUnit\Framework\TestCase;

class ExceptionHierarchyTest extends TestCase
{
    public function test_invalid_state_transition_details(): void
    {
        $e = new InvalidStateTransitionException('msg', 'A', 'B', ['C']);
        $this->assertSame('WORKFLOW_INVALID_TRANSITION', $e->getErrorCode());
        $this->assertSame(422, $e->getHttpStatus());
        $this->assertSame([
            'from_state' => 'A',
            'to_state' => 'B',
            'allowed_transitions' => ['C'],
        ], $e->getDetails());
    }

    public function test_resource_not_found_details(): void
    {
        $e = new ResourceNotFoundException('msg', 'Project', 9);
        $this->assertSame('RESOURCE_NOT_FOUND', $e->getErrorCode());
        $this->assertSame(404, $e->getHttpStatus());
        $this->assertSame(['resource' => 'Project', 'id' => 9], $e->getDetails());
    }

    public function test_payment_failed_details(): void
    {
        $e = new PaymentFailedException('msg', 't1', 'r1');
        $this->assertSame('PAYMENT_FAILED', $e->getErrorCode());
        $this->assertSame(422, $e->getHttpStatus());
        $this->assertSame(['transaction_id' => 't1', 'reason' => 'r1'], $e->getDetails());
    }

    public function test_validation_exception_implements_contract(): void
    {
        $e = new ValidationException('bad', ['x' => ['y']]);
        $this->assertInstanceOf(ExceptionContract::class, $e);
        $this->assertSame('VALIDATION_ERROR', $e->getErrorCode());
    }
}
