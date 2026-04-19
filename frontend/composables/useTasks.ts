/**
 * Composable for tasks API operations
 */
interface TaskParams {
    per_page?: number;
    page?: number;
    status?: string;
    priority?: string;
}

interface TaskData {
    [key: string]: unknown;
}

export const useTasks = () => {
    const { apiFetch } = useApi();

    const listTasks = async (params?: TaskParams) => {
        const query = new URLSearchParams();
        if (params?.per_page) query.append('per_page', String(params.per_page));
        if (params?.page) query.append('page', String(params.page));
        if (params?.status) query.append('status', params.status);
        if (params?.priority) query.append('priority', params.priority);

        const url = `/v1/tasks${query.toString() ? '?' + query.toString() : ''}`;
        const response = await apiFetch(url);
        return response;
    };

    const getTask = async (taskId: number | string) => {
        const response = await apiFetch(`/v1/tasks/${taskId}`);
        return response;
    };

    const getProjectTasks = async (projectId: number | string, params?: TaskParams) => {
        const query = new URLSearchParams();
        if (params?.per_page) query.append('per_page', String(params.per_page));
        if (params?.page) query.append('page', String(params.page));

        const url = `/v1/projects/${projectId}/tasks${query.toString() ? '?' + query.toString() : ''}`;
        const response = await apiFetch(url);
        return response;
    };

    const createTask = async (data: TaskData) => {
        const response = await apiFetch('/v1/tasks', {
            method: 'POST',
            body: JSON.stringify(data),
        });
        return response;
    };

    const updateTask = async (taskId: number | string, data: TaskData) => {
        const response = await apiFetch(`/v1/tasks/${taskId}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
        return response;
    };

    const deleteTask = async (taskId: number | string) => {
        const response = await apiFetch(`/v1/tasks/${taskId}`, {
            method: 'DELETE',
        });
        return response;
    };

    return {
        listTasks,
        getTask,
        getProjectTasks,
        createTask,
        updateTask,
        deleteTask,
    };
};
