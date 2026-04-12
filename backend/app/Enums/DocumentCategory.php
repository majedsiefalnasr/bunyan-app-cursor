<?php

namespace App\Enums;

enum DocumentCategory: string
{
    case Blueprint = 'blueprint';
    case Contract = 'contract';
    case Permit = 'permit';
    case Invoice = 'invoice';
    case Photo = 'photo';
    case Report = 'report';
    case Other = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
