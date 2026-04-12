export function useProjectWorkflow() {
    const { apiFetch } = useApi();

    async function start(projectId: string | number) {
        const id = typeof projectId === 'number' ? String(projectId) : projectId;
        return apiFetch<unknown>(`/v1/projects/${id}/workflow/start`, {
            method: 'POST',
        });
    }

    return { start };
}
