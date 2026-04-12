<?php

namespace App\Enums;

enum ProjectRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Engineer = 'engineer';
    case Worker = 'worker';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'مالك',
            self::Manager => 'مدير',
            self::Engineer => 'مهندس',
            self::Worker => 'عامل',
            self::Viewer => 'مشاهد',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
