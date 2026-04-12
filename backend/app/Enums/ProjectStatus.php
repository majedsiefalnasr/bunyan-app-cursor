<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Draft = 'draft';
    case Planning = 'planning';
    case InProgress = 'in_progress';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Planning => 'تخطيط',
            self::InProgress => 'قيد التنفيذ',
            self::OnHold => 'معلق',
            self::Completed => 'مكتمل',
            self::Closed => 'مغلق',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
