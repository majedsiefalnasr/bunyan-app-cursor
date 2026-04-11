<?php

namespace App\Enums;

enum ReportType: string
{
    case Progress = 'progress';
    case Inspection = 'inspection';
    case Incident = 'incident';
    case Completion = 'completion';

    public function label(): string
    {
        return match ($this) {
            self::Progress => 'تقرير تقدم',
            self::Inspection => 'تقرير فحص',
            self::Incident => 'تقرير حادثة',
            self::Completion => 'تقرير إتمام',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
