/**
 * Composable for transactions API operations
 */
interface TransactionParams {
    per_page?: number;
    page?: number;
    type?: string;
    status?: string;
}

export const useTransactions = () => {
    const { apiFetch } = useApi();

    const listTransactions = async (params?: TransactionParams) => {
        const query = new URLSearchParams();
        if (params?.per_page) query.append('per_page', String(params.per_page));
        if (params?.page) query.append('page', String(params.page));
        if (params?.type) query.append('type', params.type);
        if (params?.status) query.append('status', params.status);

        const url = `/api/transactions${query.toString() ? '?' + query.toString() : ''}`;
        const response = await apiFetch(url);
        return response;
    };

    const getTransaction = async (transactionId: number | string) => {
        const response = await apiFetch(`/api/transactions/${transactionId}`);
        return response;
    };

    const getProjectTransactions = async (
        projectId: number | string,
        params?: TransactionParams
    ) => {
        const query = new URLSearchParams();
        if (params?.per_page) query.append('per_page', String(params.per_page));
        if (params?.page) query.append('page', String(params.page));

        const url = `/api/projects/${projectId}/transactions${query.toString() ? '?' + query.toString() : ''}`;
        const response = await apiFetch(url);
        return response;
    };

    return {
        listTransactions,
        getTransaction,
        getProjectTransactions,
    };
};
