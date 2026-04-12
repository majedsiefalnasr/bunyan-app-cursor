<?php

namespace App\Enums;

enum NotificationType: string
{
    case General = 'general';
    case Orders = 'orders';
    case Projects = 'projects';
    case Approvals = 'approvals';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
