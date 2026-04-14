export interface AdminActivityLogQuery {
    page: number;
    perPage: number;
}

export function buildAdminActivityLogQuery(params: AdminActivityLogQuery): string {
    return new URLSearchParams({
        page: String(params.page),
        per_page: String(params.perPage),
    }).toString();
}

export function useAdminActivityLogApi() {
    const { apiFetch } = useApi();

    async function list(params: AdminActivityLogQuery) {
        return await apiFetch(`/v1/admin/activity-log?${buildAdminActivityLogQuery(params)}`);
    }

    return { list };
}
