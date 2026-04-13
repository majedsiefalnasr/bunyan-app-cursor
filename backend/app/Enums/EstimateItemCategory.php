<?php

namespace App\Enums;

enum EstimateItemCategory: string
{
    case Material = 'material';
    case Labor = 'labor';
    case Overhead = 'overhead';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
