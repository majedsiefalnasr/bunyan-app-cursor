export interface OrderRow {
    id: number;
    order_number: string | null;
    status: string;
    total_price: string;
    created_at: string | null;
}

export interface OrderDetail extends OrderRow {
    items?: Array<{
        id: number;
        product_id: number;
        quantity: number;
        price: string;
    }>;
    confirmed_at?: string | null;
    shipped_at?: string | null;
    delivered_at?: string | null;
}

interface LaravelApi<T> {
    data: T;
}

interface Paginated<T> {
    data: T[];
    meta?: { current_page: number; last_page: number; per_page: number; total: number };
}

export function useOrders() {
    const { apiFetch } = useApi();

    async function listOrders(
        query: Record<string, string | number | undefined> = {}
    ): Promise<Paginated<OrderRow>> {
        const qs = new URLSearchParams();
        for (const [k, v] of Object.entries(query)) {
            if (v === undefined || v === '') {
                continue;
            }
            qs.set(k, String(v));
        }
        const suffix = qs.toString() ? `?${qs.toString()}` : '';
        const res = await apiFetch<LaravelApi<unknown>>(`/v1/orders${suffix}`);
        const body = res.data;
        if (body && typeof body === 'object' && Array.isArray((body as Paginated<OrderRow>).data)) {
            return body as Paginated<OrderRow>;
        }
        return {
            data: Array.isArray(body) ? (body as OrderRow[]) : [],
            meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
        };
    }

    async function getOrder(id: number): Promise<OrderDetail> {
        const res = await apiFetch<LaravelApi<OrderDetail>>(`/v1/orders/${id}`);
        return res.data;
    }

    async function confirmOrder(id: number): Promise<OrderDetail> {
        const res = await apiFetch<LaravelApi<OrderDetail>>(`/v1/orders/${id}/confirm`, {
            method: 'PUT',
        });
        return res.data;
    }

    async function cancelOrder(id: number): Promise<OrderDetail> {
        const res = await apiFetch<LaravelApi<OrderDetail>>(`/v1/orders/${id}/cancel`, {
            method: 'PUT',
        });
        return res.data;
    }

    return { listOrders, getOrder, confirmOrder, cancelOrder };
}
