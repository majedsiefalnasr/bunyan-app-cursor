<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'في الانتظار',
            self::Approved => 'معتمد',
            self::Rejected => 'مرفوض',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
