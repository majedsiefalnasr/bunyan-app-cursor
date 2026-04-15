export interface AdminUsersQuery {
    page: number;
    perPage: number;
    role?: string;
}

export function buildAdminUsersQuery(params: AdminUsersQuery): string {
    const search = new URLSearchParams({
        page: String(params.page),
        per_page: String(params.perPage),
    });
    if (params.role) {
        search.set('role', params.role);
    }

    return search.toString();
}

export function useAdminUsersApi() {
    const { apiFetch } = useApi();

    async function list(params: AdminUsersQuery) {
        return await apiFetch(`/v1/admin/users?${buildAdminUsersQuery(params)}`);
    }

    async function assignRole(userId: number, role: string) {
        return await apiFetch(`/v1/admin/users/${userId}/role`, {
            method: 'POST',
            body: { role },
        });
    }

    async function removeRole(userId: number) {
        return await apiFetch(`/v1/admin/users/${userId}/role`, {
            method: 'DELETE',
        });
    }

    return { list, assignRole, removeRole };
}
