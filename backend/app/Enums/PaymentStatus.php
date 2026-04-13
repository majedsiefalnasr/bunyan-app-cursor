<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'في الانتظار',
            self::Processing => 'قيد المعالجة',
            self::Completed => 'مكتمل',
            self::Failed => 'فاشل',
            self::Refunded => 'مسترجع',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
