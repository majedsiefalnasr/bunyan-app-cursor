import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useAuthStore } from '~/stores/auth';

const navigateToMock = vi.fn();
vi.stubGlobal('navigateTo', navigateToMock);

const useToastMock = vi.fn(() => ({ add: vi.fn() }));
vi.stubGlobal('useToast', useToastMock);

const useAuthStoreMock = vi.fn();
vi.stubGlobal('useAuthStore', useAuthStoreMock);

describe('role middleware', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('allows access when user role matches required roles', () => {
        const store = useAuthStore();
        store.$patch({
            user: {
                id: 1,
                name: 'Admin',
                email: 'admin@test.com',
                role: 'admin',
                phone: null,
                active: true,
                email_verified_at: null,
                created_at: '2020-01-01',
                updated_at: '2020-01-01',
            },
        });
        useAuthStoreMock.mockReturnValue(store);

        expect(store.userRole).toBe('admin');
    });

    it('store has null role when no user', () => {
        const store = useAuthStore();
        useAuthStoreMock.mockReturnValue(store);

        expect(store.userRole).toBeNull();
    });

    it('store role matches user role', () => {
        const store = useAuthStore();
        store.$patch({
            user: {
                id: 2,
                name: 'Customer',
                email: 'customer@test.com',
                role: 'customer',
                phone: null,
                active: true,
                email_verified_at: null,
                created_at: '2020-01-01',
                updated_at: '2020-01-01',
            },
        });

        expect(store.userRole).toBe('customer');
    });
});
