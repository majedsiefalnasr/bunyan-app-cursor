<?php

namespace App\Enums;

enum TransactionType: string
{
    case Payment = 'payment';
    case Withdrawal = 'withdrawal';
    case Refund = 'refund';
    case Commission = 'commission';

    public function label(): string
    {
        return match ($this) {
            self::Payment => 'دفع',
            self::Withdrawal => 'سحب',
            self::Refund => 'استرجاع',
            self::Commission => 'عمولة',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
