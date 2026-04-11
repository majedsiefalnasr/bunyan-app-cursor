<?php

namespace App\Enums;

enum WorkflowType: string
{
    case Project = 'project';
    case Phase = 'phase';
    case Task = 'task';

    public function label(): string
    {
        return match ($this) {
            self::Project => 'مشروع',
            self::Phase => 'مرحلة',
            self::Task => 'مهمة',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
