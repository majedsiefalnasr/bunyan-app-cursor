<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'في الانتظار',
            self::Confirmed => 'مؤكد',
            self::Processing => 'قيد المعالجة',
            self::Shipped => 'تم الشحن',
            self::Delivered => 'تم التسليم',
            self::Completed => 'مكتمل',
            self::Cancelled => 'ملغى',
            self::Refunded => 'مسترجع',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
