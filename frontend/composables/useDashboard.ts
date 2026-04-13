interface LaravelApi<T> {
    data: T;
}

export interface DashboardKpis {
    users: number | null;
    projects: number | null;
    orders: number | null;
    revenue_sar: number | null;
    tasks_assigned: number | null;
    reports: number | null;
}

export interface DashboardOverview {
    role: string;
    kpis: DashboardKpis;
}

export interface DashboardMetricsPayload {
    metrics: DashboardKpis;
}

export interface ActivityLogRow {
    id: number;
    user_id: number | null;
    action: string;
    subject_type: string;
    subject_id: number;
    created_at: string | null;
    actor?: { id: number; name: string };
}

export interface PaginatedActivity {
    data: ActivityLogRow[];
    meta?: { current_page: number; last_page: number; per_page: number; total: number };
}

export function useDashboard() {
    const { apiFetch } = useApi();

    async function fetchOverview(): Promise<DashboardOverview> {
        const res = await apiFetch<LaravelApi<DashboardOverview>>('/v1/dashboard');

        return res.data;
    }

    async function fetchMetrics(): Promise<DashboardMetricsPayload> {
        const res = await apiFetch<LaravelApi<DashboardMetricsPayload>>('/v1/dashboard/metrics');

        return res.data;
    }

    async function fetchRecentActivity(perPage = 15): Promise<PaginatedActivity> {
        const res = await apiFetch<LaravelApi<PaginatedActivity>>(
            `/v1/dashboard/recent-activity?per_page=${perPage}`
        );

        return res.data;
    }

    return {
        fetchOverview,
        fetchMetrics,
        fetchRecentActivity,
    };
}
