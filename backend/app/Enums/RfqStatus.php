<?php

namespace App\Enums;

enum RfqStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Quoting = 'quoting';
    case Evaluation = 'evaluation';
    case Awarded = 'awarded';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Sent => 'مرسلة',
            self::Quoting => 'قيد التسعير',
            self::Evaluation => 'قيد التقييم',
            self::Awarded => 'تم الإرساء',
            self::Closed => 'مغلقة',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
