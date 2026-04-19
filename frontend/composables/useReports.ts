/**
 * Composable for reports API operations
 */
interface ReportParams {
    per_page?: number;
    page?: number;
    type?: string;
    status?: string;
}

interface ReportData {
    [key: string]: unknown;
}

export const useReports = () => {
    const { apiFetch } = useApi();

    const listReports = async (params?: ReportParams) => {
        const query = new URLSearchParams();
        if (params?.per_page) query.append('per_page', String(params.per_page));
        if (params?.page) query.append('page', String(params.page));
        if (params?.type) query.append('type', params.type);
        if (params?.status) query.append('status', params.status);

        const url = `/v1/reports${query.toString() ? '?' + query.toString() : ''}`;
        const response = await apiFetch(url);
        return response;
    };

    const getReport = async (reportId: number | string) => {
        const response = await apiFetch(`/v1/reports/${reportId}`);
        return response;
    };

    const getProjectReports = async (projectId: number | string, params?: ReportParams) => {
        const query = new URLSearchParams();
        if (params?.per_page) query.append('per_page', String(params.per_page));
        if (params?.page) query.append('page', String(params.page));

        const url = `/v1/projects/${projectId}/reports${query.toString() ? '?' + query.toString() : ''}`;
        const response = await apiFetch(url);
        return response;
    };

    const createReport = async (data: ReportData) => {
        const response = await apiFetch('/v1/reports', {
            method: 'POST',
            body: JSON.stringify(data),
        });
        return response;
    };

    const updateReport = async (reportId: number | string, data: ReportData) => {
        const response = await apiFetch(`/v1/reports/${reportId}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
        return response;
    };

    const deleteReport = async (reportId: number | string) => {
        const response = await apiFetch(`/v1/reports/${reportId}`, {
            method: 'DELETE',
        });
        return response;
    };

    return {
        listReports,
        getReport,
        getProjectReports,
        createReport,
        updateReport,
        deleteReport,
    };
};
