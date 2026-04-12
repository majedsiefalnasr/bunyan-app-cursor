<?php

namespace App\Enums;

enum SupplierVerificationStatus: string
{
    case Pending = 'pending';
    case Verified = 'verified';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'قيد المراجعة',
            self::Verified => 'موثّق',
            self::Suspended => 'موقوف',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
