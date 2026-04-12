import { describe, expect, it } from 'vitest';
import {
    buildProductListQueryString,
    defaultProductCatalogFilters,
    extractProductListPayload,
    productDetailSegment,
} from '~/composables/useProductCatalogQuery';

describe('useProductCatalogQuery', () => {
    it('buildProductListQueryString includes filters', () => {
        const f = defaultProductCatalogFilters();
        f.page = 2;
        f.perPage = 24;
        f.search = 'cement';
        f.categoryId = 3;
        f.minPrice = '10';
        f.maxPrice = '99';
        f.inStock = true;
        f.supplierId = 5;
        const qs = buildProductListQueryString(f);
        expect(qs).toContain('page=2');
        expect(qs).toContain('per_page=24');
        expect(qs).toContain('search=cement');
        expect(qs).toContain('category_id=3');
        expect(qs).toContain('min_price=10');
        expect(qs).toContain('max_price=99');
        expect(qs).toContain('in_stock=1');
        expect(qs).toContain('supplier_id=5');
    });

    it('extractProductListPayload handles flat array', () => {
        const { items, meta } = extractProductListPayload({ data: [{ id: 1 }] });
        expect(items).toHaveLength(1);
        expect(meta).toBeNull();
    });

    it('extractProductListPayload handles paginated envelope', () => {
        const { items, meta } = extractProductListPayload({
            data: {
                data: [{ id: 1 }],
                meta: { total: 1, last_page: 1, current_page: 1, per_page: 12 },
            },
        });
        expect(items).toHaveLength(1);
        expect(meta?.total).toBe(1);
    });

    it('productDetailSegment prefers sku', () => {
        expect(productDetailSegment({ id: 9, sku: 'SKU-ABC' })).toBe(encodeURIComponent('SKU-ABC'));
        expect(productDetailSegment({ id: 9, sku: '  ' })).toBe('9');
        expect(productDetailSegment({ id: 9 })).toBe('9');
    });
});
