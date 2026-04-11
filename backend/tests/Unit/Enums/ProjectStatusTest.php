<?php

namespace Tests\Unit\Enums;

use App\Enums\ProjectStatus;
use PHPUnit\Framework\TestCase;

class ProjectStatusTest extends TestCase
{
    public function test_has_five_cases(): void
    {
        $this->assertCount(5, ProjectStatus::cases());
    }

    public function test_backing_values(): void
    {
        $this->assertSame('pending', ProjectStatus::Pending->value);
        $this->assertSame('active', ProjectStatus::Active->value);
        $this->assertSame('on_hold', ProjectStatus::OnHold->value);
        $this->assertSame('completed', ProjectStatus::Completed->value);
        $this->assertSame('cancelled', ProjectStatus::Cancelled->value);
    }

    public function test_arabic_labels(): void
    {
        $this->assertSame('في الانتظار', ProjectStatus::Pending->label());
        $this->assertSame('نشط', ProjectStatus::Active->label());
        $this->assertSame('معلق', ProjectStatus::OnHold->label());
        $this->assertSame('مكتمل', ProjectStatus::Completed->label());
        $this->assertSame('ملغى', ProjectStatus::Cancelled->label());
    }

    public function test_values_returns_all_backing_values(): void
    {
        $expected = ['pending', 'active', 'on_hold', 'completed', 'cancelled'];
        $this->assertSame($expected, ProjectStatus::values());
    }

    public function test_from_valid_value(): void
    {
        $this->assertSame(ProjectStatus::Active, ProjectStatus::from('active'));
    }

    public function test_try_from_invalid_returns_null(): void
    {
        $this->assertNull(ProjectStatus::tryFrom('unknown'));
    }
}
