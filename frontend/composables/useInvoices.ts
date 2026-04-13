export interface InvoiceRow {
    id: number;
    invoice_number: string;
    status: string;
    status_label: string;
    total: string;
    due_date: string | null;
    created_at: string | null;
}

export interface InvoiceDetail extends InvoiceRow {
    order_id: number | null;
    customer_id: number;
    supplier_id: number | null;
    subtotal: string;
    vat_amount: string;
    vat_percentage: string;
    zatca_qr_data: string | null;
    notes: string | null;
    items?: Array<{
        id: number;
        description_ar: string | null;
        description_en: string | null;
        quantity: number;
        unit_price: string;
        line_total: string;
    }>;
}

interface LaravelApi<T> {
    data: T;
}

interface Paginated<T> {
    data: T[];
    meta?: { current_page: number; last_page: number; per_page: number; total: number };
}

export function useInvoices() {
    const { apiFetch } = useApi();
    const config = useRuntimeConfig();
    const auth = useAuthStore();

    async function listInvoices(
        query: Record<string, string | number | undefined> = {}
    ): Promise<Paginated<InvoiceRow>> {
        const qs = new URLSearchParams();
        for (const [k, v] of Object.entries(query)) {
            if (v === undefined || v === '') {
                continue;
            }
            qs.set(k, String(v));
        }
        const suffix = qs.toString() ? `?${qs.toString()}` : '';
        const res = await apiFetch<LaravelApi<unknown>>(`/v1/invoices${suffix}`);
        const body = res.data;
        if (
            body &&
            typeof body === 'object' &&
            Array.isArray((body as Paginated<InvoiceRow>).data)
        ) {
            return body as Paginated<InvoiceRow>;
        }
        return {
            data: Array.isArray(body) ? (body as InvoiceRow[]) : [],
            meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
        };
    }

    async function getInvoice(id: number): Promise<InvoiceDetail> {
        const res = await apiFetch<LaravelApi<InvoiceDetail>>(`/v1/invoices/${id}`);
        return res.data;
    }

    async function createInvoice(payload: {
        items: Array<{
            description_ar?: string;
            description_en?: string;
            quantity: number;
            unit_price: number;
            vat_rate?: number;
        }>;
        notes?: string;
        due_date?: string;
    }): Promise<InvoiceDetail> {
        const res = await apiFetch<LaravelApi<InvoiceDetail>>('/v1/invoices', {
            method: 'POST',
            body: payload,
        });
        return res.data;
    }

    async function sendInvoice(id: number): Promise<InvoiceDetail> {
        const res = await apiFetch<LaravelApi<InvoiceDetail>>(`/v1/invoices/${id}/send`, {
            method: 'POST',
        });
        return res.data;
    }

    async function downloadInvoicePdf(id: number, filename: string): Promise<void> {
        const root = ((config.public.apiBaseUrl as string) || '').replace(/\/$/, '');
        const token = auth.token;
        const res = await fetch(`${root}/v1/invoices/${id}/pdf`, {
            headers: {
                Accept: 'application/pdf',
                ...(token ? { Authorization: `Bearer ${token}` } : {}),
                'Accept-Language': 'ar',
            },
        });
        if (!res.ok) {
            throw new Error('pdf_download_failed');
        }
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    }

    return { listInvoices, getInvoice, createInvoice, sendInvoice, downloadInvoicePdf };
}
