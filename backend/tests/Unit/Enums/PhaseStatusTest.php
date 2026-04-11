<?php

namespace Tests\Unit\Enums;

use App\Enums\PhaseStatus;
use PHPUnit\Framework\TestCase;

class PhaseStatusTest extends TestCase
{
    public function test_has_five_cases(): void
    {
        $this->assertCount(5, PhaseStatus::cases());
    }

    public function test_backing_values(): void
    {
        $this->assertSame('pending', PhaseStatus::Pending->value);
        $this->assertSame('in_progress', PhaseStatus::InProgress->value);
        $this->assertSame('completed', PhaseStatus::Completed->value);
        $this->assertSame('approved', PhaseStatus::Approved->value);
        $this->assertSame('rejected', PhaseStatus::Rejected->value);
    }

    public function test_arabic_labels(): void
    {
        $this->assertSame('في الانتظار', PhaseStatus::Pending->label());
        $this->assertSame('قيد التنفيذ', PhaseStatus::InProgress->label());
        $this->assertSame('مكتملة', PhaseStatus::Completed->label());
        $this->assertSame('معتمدة', PhaseStatus::Approved->label());
        $this->assertSame('مرفوضة', PhaseStatus::Rejected->label());
    }

    public function test_values_returns_all_backing_values(): void
    {
        $expected = ['pending', 'in_progress', 'completed', 'approved', 'rejected'];
        $this->assertSame($expected, PhaseStatus::values());
    }

    public function test_from_valid_value(): void
    {
        $this->assertSame(PhaseStatus::InProgress, PhaseStatus::from('in_progress'));
    }

    public function test_try_from_invalid_returns_null(): void
    {
        $this->assertNull(PhaseStatus::tryFrom('unknown'));
    }
}
