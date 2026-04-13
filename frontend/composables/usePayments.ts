import { useApi } from '~/composables/useApi';

interface LaravelApi<T> {
    success: boolean;
    data: T;
    message?: string | null;
}

export type PaymentRow = {
    id: number;
    payable_type: string;
    payable_id: number;
    amount: string;
    currency: string;
    method: string;
    status: string;
    gateway_reference: string | null;
    paid_at: string | null;
    created_at: string | null;
};

type Paginated<T> = {
    data: T[];
    meta?: { current_page?: number; last_page?: number; total?: number };
};

export function usePayments() {
    const { apiFetch } = useApi();

    async function listHistory(perPage = 15): Promise<Paginated<PaymentRow>> {
        const res = await apiFetch<LaravelApi<unknown>>(`/v1/payments/history?per_page=${perPage}`);
        const body = res.data;
        if (
            body &&
            typeof body === 'object' &&
            Array.isArray((body as Paginated<PaymentRow>).data)
        ) {
            return body as Paginated<PaymentRow>;
        }
        return {
            data: Array.isArray(body) ? (body as PaymentRow[]) : [],
            meta: { current_page: 1, last_page: 1, total: 0 },
        };
    }

    async function getPayment(id: number): Promise<{ data: PaymentRow }> {
        const res = await apiFetch<LaravelApi<PaymentRow>>(`/v1/payments/${id}`);
        return { data: res.data };
    }

    async function initiate(payload: {
        payable_type: string;
        payable_id: number;
        method: string;
    }): Promise<{ data: PaymentRow }> {
        const res = await apiFetch<LaravelApi<PaymentRow>>('/v1/payments/initiate', {
            method: 'POST',
            body: payload,
        });
        return { data: res.data };
    }

    return { listHistory, getPayment, initiate };
}
