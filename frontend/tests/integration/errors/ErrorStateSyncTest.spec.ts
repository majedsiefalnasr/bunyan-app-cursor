import { createPinia, setActivePinia } from 'pinia';
import { describe, expect, it, vi } from 'vitest';

import { useErrorStore } from '~/stores/error';

describe('error state sync', () => {
    it('handles multiple errors then clears', () => {
        vi.useFakeTimers();
        setActivePinia(createPinia());
        const store = useErrorStore();

        store.addError({ code: 'A', message: '1', timestamp: 1 });
        store.addError({ code: 'B', message: '2', timestamp: 2 });
        expect(store.errors.length).toBe(2);

        store.clearErrors();
        expect(store.errors.length).toBe(0);
    });
});
