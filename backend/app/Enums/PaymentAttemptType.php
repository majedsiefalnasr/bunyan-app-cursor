<?php

namespace App\Enums;

enum PaymentAttemptType: string
{
    case Charge = 'charge';
    case Refund = 'refund';
    case Void = 'void';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
