export interface RfqItem {
    id: number;
    product_id: number | null;
    description: string;
    quantity: number;
    unit: string;
    specifications?: unknown;
    sort_order?: number | null;
}

export interface RfqDetail {
    id: number;
    project_id: number | null;
    created_by: number;
    title: string;
    description: string | null;
    status: string;
    status_label: string | null;
    delivery_deadline: string | null;
    response_deadline: string | null;
    sent_at: string | null;
    awarded_quotation_id: number | null;
    awarded_by: number | null;
    awarded_at: string | null;
    closed_at: string | null;
    items?: RfqItem[];
    created_at: string | null;
    updated_at: string | null;
}

export interface QuotationRow {
    id: number;
    rfq_id: number;
    supplier_id: number;
    status: string;
    status_label: string | null;
    total_price: string;
    delivery_days: number | null;
    notes: string | null;
    valid_until: string | null;
    submitted_at: string | null;
    supplier_profile?: {
        id: number;
        company_name_ar: string | null;
        company_name_en: string | null;
    } | null;
}

export interface CompareCell {
    unit_price: number;
    total_price: number;
    notes: string | null;
}

export type CompareCells = Record<string, Record<string, CompareCell>>;

export interface ComparePayload {
    items: RfqItem[];
    quotations: QuotationRow[];
    cells: CompareCells;
}

export interface Paginated<T> {
    data: T[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
