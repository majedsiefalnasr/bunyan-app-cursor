import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useAuth } from '~/composables/useAuth';
import { useAuthStore } from '~/stores/auth';
import type { UserProfile } from '~/types/auth';

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
        };
        store.setUser(mockUser);
        const { role } = useAuth();
        expect(role.value).toBe('contractor');
    });

    it('hasRole returns true for matching role', () => {
        const store = useAuthStore();
        store.setUser({ id: 1, name: 'Test', email: 't@t.com', role: 'admin' });
        const { hasRole } = useAuth();
        expect(hasRole('admin')).toBe(true);
        expect(hasRole('customer', 'admin')).toBe(true);
    });

    it('hasRole returns false for non-matching role', () => {
        const store = useAuthStore();
        store.setUser({ id: 1, name: 'Test', email: 't@t.com', role: 'customer' });
        const { hasRole } = useAuth();
        expect(hasRole('admin')).toBe(false);
    });

    it('logout clears token and user and navigates to login', async () => {
        const store = useAuthStore();
        store.setToken('test-token');
        store.setUser({ id: 1, name: 'Test', email: 't@t.com', role: 'customer' });

        const { logout } = useAuth();
        await logout();

        expect(store.token).toBeNull();
        expect(store.user).toBeNull();
        expect(navigateToMock).toHaveBeenCalledWith('/ar/auth/login');
    });

    it('user is reactive — updates when store changes', () => {
        const store = useAuthStore();
        const { user } = useAuth();

        expect(user.value).toBeNull();
        store.setUser({ id: 2, name: 'Sara', email: 's@s.com', role: 'architect' });
        expect(user.value?.name).toBe('Sara');
    });
});
