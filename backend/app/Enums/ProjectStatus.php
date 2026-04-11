<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case OnHold = 'on_hold';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'في الانتظار',
            self::Active => 'نشط',
            self::OnHold => 'معلق',
            self::Completed => 'مكتمل',
            self::Cancelled => 'ملغى',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
