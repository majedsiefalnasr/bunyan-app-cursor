<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case InReview = 'in_review';
    case Done = 'done';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'قائمة',
            self::InProgress => 'قيد التنفيذ',
            self::InReview => 'قيد المراجعة',
            self::Done => 'منجزة',
            self::Blocked => 'متوقفة',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
