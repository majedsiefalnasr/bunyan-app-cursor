import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import QuotationComparisonTable from '~/components/rfq/QuotationComparisonTable.vue';

describe('QuotationComparisonTable', () => {
    const baseProps = {
        items: [
            {
                id: 1,
                product_id: null,
                description: 'اسمنت',
                quantity: 10,
                unit: 'كيس',
            },
        ],
        quotations: [
            {
                id: 9,
                rfq_id: 1,
                supplier_id: 2,
                status: 'submitted',
                status_label: null,
                total_price: '100.00',
                delivery_days: null,
                notes: null,
                valid_until: null,
                submitted_at: null,
                supplier_profile: { id: 2, company_name_ar: 'مورد أ', company_name_en: null },
            },
        ],
        cells: {
            '9': {
                '1': { unit_price: 10, total_price: 100, notes: null },
            },
        },
    };

    it('applies dir attribute for RTL layout', () => {
        const w = mount(QuotationComparisonTable, {
            props: { ...baseProps, dir: 'rtl' },
        });
        expect(w.find('[data-testid="quotation-compare-wrap"]').attributes('dir')).toBe('rtl');
    });

    it('applies dir attribute for LTR layout', () => {
        const w = mount(QuotationComparisonTable, {
            props: { ...baseProps, dir: 'ltr' },
        });
        expect(w.find('[data-testid="quotation-compare-wrap"]').attributes('dir')).toBe('ltr');
    });

    it('renders item description in document order', () => {
        const w = mount(QuotationComparisonTable, {
            props: { ...baseProps, dir: 'rtl' },
        });
        expect(w.text()).toContain('اسمنت');
        expect(w.text()).toContain('10');
    });
});
