<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'في الانتظار',
            self::Processing => 'قيد المعالجة',
            self::Shipped => 'تم الشحن',
            self::Delivered => 'تم التسليم',
            self::Cancelled => 'ملغى',
            self::Refunded => 'مسترجع',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
