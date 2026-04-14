export type AnalyticsBucket = 'day' | 'week' | 'month';

export type AnalyticsMetricKey =
    | 'platform.active_users'
    | 'platform.new_registrations'
    | 'platform.session_duration_avg_seconds'
    | 'commerce.gmv'
    | 'commerce.order_volume'
    | 'commerce.conversion_rate'
    | 'commerce.avg_order_value'
    | 'projects.new_projects'
    | 'projects.completion_rate'
    | 'projects.avg_duration_days'
    | 'suppliers.new_suppliers'
    | 'suppliers.verification_rate'
    | 'suppliers.avg_response_time_seconds';

export interface ApiEnvelope<T> {
    success: boolean;
    data: T;
    message?: string | null;
    errors?: unknown;
    error?: {
        code?: string;
        message?: string;
        details?: Record<string, unknown> | null;
    };
}

export type AnalyticsOverviewResponse = ApiEnvelope<{
    range: { from: string; to: string; bucket: AnalyticsBucket };
    compare: { mode: string };
    kpis: Array<{
        key: AnalyticsMetricKey | string;
        label: string;
        value: number | null;
        delta: { value: number | null; pct: number | null } | null;
    }>;
}>;

export type AnalyticsSeriesResponse = ApiEnvelope<{
    range: { from: string; to: string; bucket: AnalyticsBucket };
    key: AnalyticsMetricKey | string;
    bucket: AnalyticsBucket;
    series: Array<{ t: string; v: number | null }>;
}>;

export type AnalyticsTrendsResponse = ApiEnvelope<{
    range: { from: string; to: string; bucket: AnalyticsBucket };
    bucket: AnalyticsBucket;
    series: Array<{
        key: AnalyticsMetricKey | string;
        points: Array<{ t: string; v: number | null }>;
    }>;
}>;
