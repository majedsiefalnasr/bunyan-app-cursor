import { ref, type Ref } from 'vue';
import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useBreadcrumb } from '~/composables/useBreadcrumb';
import type { BreadcrumbItem } from '~/composables/useBreadcrumb';

vi.stubGlobal('useState', (_key: string, init: () => BreadcrumbItem[]) => ref(init()));
vi.stubGlobal('readonly', <T>(v: T) => v);

describe('useBreadcrumb', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    function getItems(items: unknown): BreadcrumbItem[] {
        return (items as Ref<BreadcrumbItem[]>).value;
    }

    it('starts with empty items array', () => {
        const { items } = useBreadcrumb();
        expect(getItems(items)).toEqual([]);
    });

    it('setBreadcrumb sets items correctly', () => {
        const { items, setBreadcrumb } = useBreadcrumb();
        const newItems: BreadcrumbItem[] = [
            { label: 'الرئيسية', to: '/' },
            { label: 'المشاريع', to: '/projects' },
        ];
        setBreadcrumb(newItems);
        expect(getItems(items)).toEqual(newItems);
    });

    it('clearBreadcrumb resets to empty array', () => {
        const { items, setBreadcrumb, clearBreadcrumb } = useBreadcrumb();
        setBreadcrumb([{ label: 'Test' }]);
        clearBreadcrumb();
        expect(getItems(items)).toEqual([]);
    });

    it('multiple setBreadcrumb calls replace (not append) items', () => {
        const { items, setBreadcrumb } = useBreadcrumb();
        setBreadcrumb([{ label: 'First' }]);
        setBreadcrumb([{ label: 'Second' }, { label: 'Third' }]);
        expect(getItems(items)).toHaveLength(2);
        expect(getItems(items)[0].label).toBe('Second');
    });

    it('setBreadcrumb with empty array clears breadcrumb', () => {
        const { items, setBreadcrumb } = useBreadcrumb();
        setBreadcrumb([{ label: 'Test' }]);
        setBreadcrumb([]);
        expect(getItems(items)).toEqual([]);
    });
});
