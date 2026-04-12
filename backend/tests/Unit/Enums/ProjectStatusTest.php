<?php

namespace Tests\Unit\Enums;

use App\Enums\ProjectStatus;
use Tests\TestCase;

class ProjectStatusTest extends TestCase
{
    public function test_all_cases_exist(): void
    {
        $this->assertCount(6, ProjectStatus::cases());
    }

    public function test_values(): void
    {
        $this->assertSame('draft', ProjectStatus::Draft->value);
        $this->assertSame('planning', ProjectStatus::Planning->value);
        $this->assertSame('in_progress', ProjectStatus::InProgress->value);
        $this->assertSame('on_hold', ProjectStatus::OnHold->value);
        $this->assertSame('completed', ProjectStatus::Completed->value);
        $this->assertSame('closed', ProjectStatus::Closed->value);
    }

    public function test_labels_are_arabic(): void
    {
        $this->assertSame('مسودة', ProjectStatus::Draft->label());
        $this->assertSame('تخطيط', ProjectStatus::Planning->label());
        $this->assertSame('قيد التنفيذ', ProjectStatus::InProgress->label());
        $this->assertSame('معلق', ProjectStatus::OnHold->label());
        $this->assertSame('مكتمل', ProjectStatus::Completed->label());
        $this->assertSame('مغلق', ProjectStatus::Closed->label());
    }

    public function test_values_array(): void
    {
        $expected = ['draft', 'planning', 'in_progress', 'on_hold', 'completed', 'closed'];
        $this->assertSame($expected, ProjectStatus::values());
    }

    public function test_from_string(): void
    {
        $this->assertSame(ProjectStatus::InProgress, ProjectStatus::from('in_progress'));
    }

    public function test_try_from_invalid(): void
    {
        $this->assertNull(ProjectStatus::tryFrom('unknown'));
    }
}
