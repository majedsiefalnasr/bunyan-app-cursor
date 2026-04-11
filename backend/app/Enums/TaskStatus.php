<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'في الانتظار',
            self::InProgress => 'قيد التنفيذ',
            self::Completed => 'مكتملة',
            self::Approved => 'معتمدة',
            self::Rejected => 'مرفوضة',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
