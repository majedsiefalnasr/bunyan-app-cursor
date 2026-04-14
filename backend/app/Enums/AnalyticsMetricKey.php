<?php

namespace App\Enums;

enum AnalyticsMetricKey: string
{
    case PlatformActiveUsers = 'platform.active_users';
    case PlatformNewRegistrations = 'platform.new_registrations';
    case PlatformSessionDurationAvgSeconds = 'platform.session_duration_avg_seconds';

    case CommerceGmv = 'commerce.gmv';
    case CommerceOrderVolume = 'commerce.order_volume';
    case CommerceConversionRate = 'commerce.conversion_rate';
    case CommerceAvgOrderValue = 'commerce.avg_order_value';

    case ProjectsNewProjects = 'projects.new_projects';
    case ProjectsCompletionRate = 'projects.completion_rate';
    case ProjectsAvgDurationDays = 'projects.avg_duration_days';

    case SuppliersNewSuppliers = 'suppliers.new_suppliers';
    case SuppliersVerificationRate = 'suppliers.verification_rate';
    case SuppliersAvgResponseTimeSeconds = 'suppliers.avg_response_time_seconds';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $k) => $k->value, self::cases());
    }
}
