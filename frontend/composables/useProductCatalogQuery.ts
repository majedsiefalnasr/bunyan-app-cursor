export type ProductCatalogFilters = {
    page: number;
    perPage: number;
    search: string;
    categoryId: number | null;
    minPrice: string;
    maxPrice: string;
    inStock: boolean;
    supplierId: number | null;
};

export type ProductListMeta = {
    total: number;
    last_page: number;
    current_page: number;
    per_page: number;
};

export function defaultProductCatalogFilters(): ProductCatalogFilters {
    return {
        page: 1,
        perPage: 12,
        search: '',
        categoryId: null,
        minPrice: '',
        maxPrice: '',
        inStock: false,
        supplierId: null,
    };
}

export function buildProductListQueryString(f: ProductCatalogFilters): string {
    const p = new URLSearchParams();
    p.set('page', String(f.page));
    p.set('per_page', String(f.perPage));
    const q = f.search.trim();
    if (q) {
        p.set('search', q);
    }
    if (f.categoryId !== null) {
        p.set('category_id', String(f.categoryId));
    }
    if (f.minPrice.trim()) {
        p.set('min_price', f.minPrice.trim());
    }
    if (f.maxPrice.trim()) {
        p.set('max_price', f.maxPrice.trim());
    }
    if (f.inStock) {
        p.set('in_stock', '1');
    }
    if (f.supplierId !== null) {
        p.set('supplier_id', String(f.supplierId));
    }
    return p.toString();
}

export function extractProductListPayload(res: { data?: unknown }): {
    items: unknown[];
    meta: ProductListMeta | null;
} {
    const data = res.data;
    if (Array.isArray(data)) {
        return { items: data, meta: null };
    }
    if (data && typeof data === 'object' && Array.isArray((data as { data?: unknown }).data)) {
        const inner = data as {
            data: unknown[];
            meta?: ProductListMeta;
        };
        return {
            items: inner.data,
            meta: inner.meta ?? null,
        };
    }
    return { items: [], meta: null };
}

export function productDetailSegment(product: { id: number; sku?: string | null }): string {
    const sku = product.sku?.trim();
    if (sku) {
        return encodeURIComponent(sku);
    }
    return String(product.id);
}
