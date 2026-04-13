<?php

namespace App\Enums;

enum QuotationStatus: string
{
    case Submitted = 'submitted';
    case Revised = 'revised';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'مُقدم',
            self::Revised => 'مُعدل',
            self::Accepted => 'مقبول',
            self::Rejected => 'مرفوض',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
