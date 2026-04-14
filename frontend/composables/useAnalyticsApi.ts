import type {
    AnalyticsMetricKey,
    AnalyticsOverviewResponse,
    AnalyticsSeriesResponse,
    AnalyticsTrendsResponse,
} from '~/types/analytics';

export function useAnalyticsApi() {
    const { apiFetch } = useApi();

    async function overview(params?: {
        from?: string;
        to?: string;
        bucket?: 'day' | 'week' | 'month';
        compare?: 'none' | 'previous_period' | 'previous_year';
    }) {
        return await apiFetch<AnalyticsOverviewResponse>('/v1/analytics/overview', {
            query: params,
        });
    }

    async function metric(
        metric: AnalyticsMetricKey,
        params?: { from?: string; to?: string; bucket?: 'day' | 'week' | 'month' }
    ) {
        return await apiFetch<AnalyticsSeriesResponse>(`/v1/analytics/metrics/${metric}`, {
            query: params,
        });
    }

    async function trends(params: {
        keys: AnalyticsMetricKey[];
        from?: string;
        to?: string;
        bucket?: 'day' | 'week' | 'month';
    }) {
        return await apiFetch<AnalyticsTrendsResponse>('/v1/analytics/trends', { query: params });
    }

    return { overview, metric, trends };
}
