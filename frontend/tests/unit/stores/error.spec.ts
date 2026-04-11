import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useErrorStore } from '~/stores/error';

describe('error store', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        setActivePinia(createPinia());
    });

    it('adds errors and updates lastError', () => {
        const store = useErrorStore();
        store.addError({ code: 'X', message: 'm', timestamp: 1 });
        expect(store.errors.length).toBe(1);
        expect(store.lastError?.code).toBe('X');
        expect(store.hasErrors).toBe(true);
    });

    it('clears errors', () => {
        const store = useErrorStore();
        store.addError({ code: 'X', message: 'm', timestamp: 1 });
        store.clearErrors();
        expect(store.errors.length).toBe(0);
        expect(store.lastError).toBeNull();
    });

    it('auto clears after 30s', () => {
        const store = useErrorStore();
        store.addError({ code: 'X', message: 'm', timestamp: 1 });
        vi.advanceTimersByTime(30_000);
        expect(store.errors.length).toBe(0);
    });
});
