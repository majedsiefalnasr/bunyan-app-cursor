<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Void = 'void';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Sent => 'مرسلة',
            self::Paid => 'مدفوعة',
            self::Overdue => 'متأخرة',
            self::Void => 'ملغاة',
        };
    }
}
