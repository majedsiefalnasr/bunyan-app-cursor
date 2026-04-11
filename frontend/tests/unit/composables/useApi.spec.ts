import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useApi } from '~/composables/useApi';

describe('useApi', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('creates apiFetch client', () => {
        const { apiFetch } = useApi();
        expect(apiFetch).toBeTypeOf('function');
    });
});
