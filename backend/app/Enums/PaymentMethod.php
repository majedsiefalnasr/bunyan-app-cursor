<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Card = 'card';
    case Mada = 'mada';
    case BankTransfer = 'bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::Card => 'بطاقة',
            self::Mada => 'مدى',
            self::BankTransfer => 'تحويل بنكي',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
