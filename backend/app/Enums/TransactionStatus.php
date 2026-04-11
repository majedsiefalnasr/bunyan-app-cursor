<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'في الانتظار',
            self::Completed => 'مكتملة',
            self::Failed => 'فاشلة',
            self::Cancelled => 'ملغاة',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
