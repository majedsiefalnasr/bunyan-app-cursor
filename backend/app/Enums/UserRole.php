<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Contractor = 'contractor';
    case SupervisingArchitect = 'supervising_architect';
    case FieldEngineer = 'field_engineer';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'العميل',
            self::Contractor => 'المقاول',
            self::SupervisingArchitect => 'المهندس المشرف',
            self::FieldEngineer => 'المهندس الميداني',
            self::Admin => 'الإدارة',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
