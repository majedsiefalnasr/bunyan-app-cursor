<?php

namespace Tests\Unit\Enums;

use App\Enums\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskStatusTest extends TestCase
{
    public function test_has_five_cases(): void
    {
        $this->assertCount(5, TaskStatus::cases());
    }

    public function test_backing_values(): void
    {
        $this->assertSame('pending', TaskStatus::Pending->value);
        $this->assertSame('in_progress', TaskStatus::InProgress->value);
        $this->assertSame('completed', TaskStatus::Completed->value);
        $this->assertSame('approved', TaskStatus::Approved->value);
        $this->assertSame('rejected', TaskStatus::Rejected->value);
    }

    public function test_arabic_labels(): void
    {
        $this->assertSame('في الانتظار', TaskStatus::Pending->label());
        $this->assertSame('قيد التنفيذ', TaskStatus::InProgress->label());
        $this->assertSame('مكتملة', TaskStatus::Completed->label());
        $this->assertSame('معتمدة', TaskStatus::Approved->label());
        $this->assertSame('مرفوضة', TaskStatus::Rejected->label());
    }

    public function test_values_returns_all_backing_values(): void
    {
        $expected = ['pending', 'in_progress', 'completed', 'approved', 'rejected'];
        $this->assertSame($expected, TaskStatus::values());
    }

    public function test_from_valid_value(): void
    {
        $this->assertSame(TaskStatus::Completed, TaskStatus::from('completed'));
    }

    public function test_try_from_invalid_returns_null(): void
    {
        $this->assertNull(TaskStatus::tryFrom('done'));
    }
}
