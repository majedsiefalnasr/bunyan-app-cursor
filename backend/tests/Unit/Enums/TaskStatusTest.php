<?php

namespace Tests\Unit\Enums;

use App\Enums\TaskStatus;
use PHPUnit\Framework\TestCase;

class TaskStatusTest extends TestCase
{
    public function test_it_has_five_cases(): void
    {
        $this->assertCount(5, TaskStatus::cases());
    }

    public function test_values_map_to_expected_strings(): void
    {
        $this->assertSame('todo', TaskStatus::Todo->value);
        $this->assertSame('in_progress', TaskStatus::InProgress->value);
        $this->assertSame('in_review', TaskStatus::InReview->value);
        $this->assertSame('done', TaskStatus::Done->value);
        $this->assertSame('blocked', TaskStatus::Blocked->value);
    }

    public function test_labels_are_arabic(): void
    {
        $this->assertSame('قائمة', TaskStatus::Todo->label());
        $this->assertSame('قيد التنفيذ', TaskStatus::InProgress->label());
        $this->assertSame('قيد المراجعة', TaskStatus::InReview->label());
        $this->assertSame('منجزة', TaskStatus::Done->label());
        $this->assertSame('متوقفة', TaskStatus::Blocked->label());
    }

    public function test_values_returns_all_status_strings(): void
    {
        $expected = ['todo', 'in_progress', 'in_review', 'done', 'blocked'];
        $this->assertSame($expected, TaskStatus::values());
    }

    public function test_from_accepts_valid_strings(): void
    {
        $this->assertSame(TaskStatus::Done, TaskStatus::from('done'));
        $this->assertSame(TaskStatus::Todo, TaskStatus::from('todo'));
    }

    public function test_try_from_rejects_unknown_strings(): void
    {
        $this->assertNull(TaskStatus::tryFrom('completed'));
        $this->assertNull(TaskStatus::tryFrom('pending'));
    }
}
