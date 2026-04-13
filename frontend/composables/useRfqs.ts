import type { ComparePayload, Paginated, QuotationRow, RfqDetail } from '~/types/rfq';

interface LaravelApi<T> {
    success: boolean;
    data: T;
    message?: string | null;
}

export function useRfqs() {
    const { apiFetch } = useApi();

    async function listRfqs(
        query: Record<string, string | number | undefined> = {}
    ): Promise<Paginated<RfqDetail>> {
        const qs = new URLSearchParams();
        for (const [k, v] of Object.entries(query)) {
            if (v === undefined || v === '') {
                continue;
            }
            qs.set(k, String(v));
        }
        const suffix = qs.toString() ? `?${qs.toString()}` : '';
        const res = await apiFetch<LaravelApi<unknown>>(`/v1/rfqs${suffix}`);
        const body = res.data;
        if (
            body &&
            typeof body === 'object' &&
            Array.isArray((body as Paginated<RfqDetail>).data)
        ) {
            return body as Paginated<RfqDetail>;
        }
        return {
            data: Array.isArray(body) ? (body as RfqDetail[]) : [],
            meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
        };
    }

    async function getRfq(id: number): Promise<RfqDetail> {
        const res = await apiFetch<LaravelApi<RfqDetail>>(`/v1/rfqs/${id}`);
        return res.data;
    }

    async function createRfq(payload: {
        title: string;
        description?: string;
        project_id?: number | null;
        delivery_deadline?: string | null;
        response_deadline?: string | null;
        items: Array<{
            description: string;
            quantity: number;
            unit: string;
            product_id?: number | null;
        }>;
    }): Promise<RfqDetail> {
        const res = await apiFetch<LaravelApi<RfqDetail>>(`/v1/rfqs`, {
            method: 'POST',
            body: payload,
        });
        return res.data;
    }

    async function sendRfq(
        id: number,
        body: { response_deadline?: string } = {}
    ): Promise<RfqDetail> {
        const res = await apiFetch<LaravelApi<RfqDetail>>(`/v1/rfqs/${id}/send`, {
            method: 'POST',
            body,
        });
        return res.data;
    }

    async function beginEvaluation(id: number): Promise<RfqDetail> {
        const res = await apiFetch<LaravelApi<RfqDetail>>(`/v1/rfqs/${id}/evaluate`, {
            method: 'POST',
        });
        return res.data;
    }

    async function closeRfq(id: number): Promise<RfqDetail> {
        const res = await apiFetch<LaravelApi<RfqDetail>>(`/v1/rfqs/${id}/close`, {
            method: 'POST',
        });
        return res.data;
    }

    async function compareRfq(id: number): Promise<ComparePayload> {
        const res = await apiFetch<LaravelApi<ComparePayload>>(`/v1/rfqs/${id}/compare`);
        return res.data;
    }

    async function listQuotations(
        rfqId: number,
        query: Record<string, string | number | undefined> = {}
    ): Promise<Paginated<QuotationRow>> {
        const qs = new URLSearchParams();
        for (const [k, v] of Object.entries(query)) {
            if (v === undefined || v === '') {
                continue;
            }
            qs.set(k, String(v));
        }
        const suffix = qs.toString() ? `?${qs.toString()}` : '';
        const res = await apiFetch<LaravelApi<unknown>>(`/v1/rfqs/${rfqId}/quotations${suffix}`);
        const body = res.data;
        if (
            body &&
            typeof body === 'object' &&
            Array.isArray((body as Paginated<QuotationRow>).data)
        ) {
            return body as Paginated<QuotationRow>;
        }
        return {
            data: Array.isArray(body) ? (body as QuotationRow[]) : [],
            meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
        };
    }

    async function submitQuotation(
        rfqId: number,
        payload: {
            items: Array<{ rfq_item_id: number; unit_price: number; notes?: string | null }>;
            delivery_days?: number | null;
            notes?: string | null;
            valid_until?: string | null;
        }
    ): Promise<QuotationRow> {
        const res = await apiFetch<LaravelApi<QuotationRow>>(`/v1/rfqs/${rfqId}/quotations`, {
            method: 'POST',
            body: payload,
        });
        return res.data;
    }

    async function acceptQuotation(rfqId: number, quotationId: number): Promise<RfqDetail> {
        const res = await apiFetch<LaravelApi<RfqDetail>>(
            `/v1/rfqs/${rfqId}/quotations/${quotationId}/accept`,
            {
                method: 'PUT',
            }
        );
        return res.data;
    }

    return {
        listRfqs,
        getRfq,
        createRfq,
        sendRfq,
        beginEvaluation,
        closeRfq,
        compareRfq,
        listQuotations,
        submitQuotation,
        acceptQuotation,
    };
}
