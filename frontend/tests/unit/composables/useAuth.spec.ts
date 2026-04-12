import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useAuth } from '~/composables/useAuth';
import { useAuthStore } from '~/stores/auth';
import type { UserProfile } from '~/types/auth';

const profileDefaults: Pick<
    UserProfile,
    'phone' | 'active' | 'email_verified_at' | 'created_at' | 'updated_at'
> = {
    phone: null,
    active: true,
    email_verified_at: null,
    created_at: '2020-01-01T00:00:00.000000Z',
    updated_at: '2020-01-01T00:00:00.000000Z',
};

const navigateToMock = vi.fn();
vi.stubGlobal('navigateTo', navigateToMock);

describe('useAuth', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('isAuthenticated is false when token is null', () => {
        const { isAuthenticated } = useAuth();
        expect(isAuthenticated.value).toBe(false);
    });

    it('isAuthenticated is true when token is set', () => {
        const store = useAuthStore();
        store.setToken('test-token-123');
        const { isAuthenticated } = useAuth();
        expect(isAuthenticated.value).toBe(true);
    });

    it('role returns null when no user is set', () => {
        const { role } = useAuth();
        expect(role.value).toBeNull();
    });

    it('role returns user.role when user is set', () => {
        const store = useAuthStore();
        const mockUser: UserProfile = {
            id: 1,
            name: 'أحمد',
            email: 'ahmed@example.com',
            role: 'contractor',
            ...profileDefaults,
        };
        store.setUser(mockUser);
        const { role } = useAuth();
        expect(role.value).toBe('contractor');
    });

    it('hasRole returns true for matching role', () => {
        const store = useAuthStore();
        store.setUser({ id: 1, name: 'Test', email: 't@t.com', role: 'admin', ...profileDefaults });
        const { hasRole } = useAuth();
        expect(hasRole('admin')).toBe(true);
        expect(hasRole('customer', 'admin')).toBe(true);
    });

    it('hasRole returns false for non-matching role', () => {
        const store = useAuthStore();
        store.setUser({
            id: 1,
            name: 'Test',
            email: 't@t.com',
            role: 'customer',
            ...profileDefaults,
        });
        const { hasRole } = useAuth();
        expect(hasRole('admin')).toBe(false);
    });

    it('hasPermission reflects store permissions', () => {
        const store = useAuthStore();
        store.setUser({
            id: 1,
            name: 'Test',
            email: 't@t.com',
            role: 'admin',
            permissions: ['reports.create', 'reports.view'],
            ...profileDefaults,
        });
        const { hasPermission, hasAnyPermission } = useAuth();
        expect(hasPermission('reports.view')).toBe(true);
        expect(hasPermission('reports.delete')).toBe(false);
        expect(hasAnyPermission(['reports.delete', 'reports.create'])).toBe(true);
    });

    it('logout clears token and user and navigates to login', async () => {
        const store = useAuthStore();
        store.setToken('test-token');
        store.setUser({
            id: 1,
            name: 'Test',
            email: 't@t.com',
            role: 'customer',
            ...profileDefaults,
        });

        const { logout } = useAuth();
        await logout();

        expect(store.token).toBeNull();
        expect(store.user).toBeNull();
        expect(store.permissions).toEqual([]);
        expect(navigateToMock).toHaveBeenCalledWith('/ar/auth/login');
    });

    it('user is reactive — updates when store changes', () => {
        const store = useAuthStore();
        const { user } = useAuth();

        expect(user.value).toBeNull();
        store.setUser({
            id: 2,
            name: 'Sara',
            email: 's@s.com',
            role: 'supervising_architect',
            ...profileDefaults,
        });
        expect(user.value?.name).toBe('Sara');
    });
});
