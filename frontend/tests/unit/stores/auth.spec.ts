import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useAuthStore } from '~/stores/auth';

const login = vi.fn();
const register = vi.fn();
const logoutApi = vi.fn();
const getProfile = vi.fn();
const updateProfile = vi.fn();

vi.mock('~/composables/useAuthApi', () => ({
    useAuthApi: () => ({
        login,
        register,
        logout: logoutApi,
        getProfile,
        updateProfile,
        forgotPassword: vi.fn(),
        resetPassword: vi.fn(),
        verifyEmail: vi.fn(),
        resendEmailVerification: vi.fn(),
    }),
}));

describe('useAuthStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        vi.clearAllMocks();
    });

    it('login stores token and user', async () => {
        login.mockResolvedValueOnce({
            token: 'jwt',
            user: {
                id: 1,
                name: 'Test',
                email: 't@t.com',
                role: 'customer',
                phone: null,
                active: true,
                email_verified_at: null,
                created_at: '2020-01-01T00:00:00.000000Z',
                updated_at: '2020-01-01T00:00:00.000000Z',
            },
        });
        const store = useAuthStore();
        await store.login({ email: 't@t.com', password: 'password1' });
        expect(store.token).toBe('jwt');
        expect(store.user?.email).toBe('t@t.com');
        expect(store.isAuthenticated).toBe(true);
    });

    it('logout clears state and calls API when token exists', async () => {
        logoutApi.mockResolvedValueOnce(undefined);
        const store = useAuthStore();
        store.setToken('jwt');
        store.setUser({
            id: 1,
            name: 'Test',
            email: 't@t.com',
            role: 'customer',
            phone: null,
            active: true,
            email_verified_at: null,
            created_at: '2020-01-01T00:00:00.000000Z',
            updated_at: '2020-01-01T00:00:00.000000Z',
        });
        await store.logout();
        expect(logoutApi).toHaveBeenCalled();
        expect(store.token).toBeNull();
        expect(store.user).toBeNull();
    });

    it('register persists returned session', async () => {
        register.mockResolvedValueOnce({
            token: 'reg',
            user: {
                id: 3,
                name: 'New',
                email: 'n@n.com',
                role: 'customer',
                phone: null,
                active: true,
                email_verified_at: null,
                created_at: '2020-01-01T00:00:00.000000Z',
                updated_at: '2020-01-01T00:00:00.000000Z',
            },
        });
        const store = useAuthStore();
        await store.register({
            name: 'New',
            email: 'n@n.com',
            password: 'password1',
            password_confirmation: 'password1',
        });
        expect(store.token).toBe('reg');
        expect(store.user?.name).toBe('New');
    });

    it('fetchUser returns null when no token', async () => {
        const store = useAuthStore();
        const result = await store.fetchUser();
        expect(result).toBeNull();
        expect(getProfile).not.toHaveBeenCalled();
    });

    it('fetchUser returns null on API failure', async () => {
        getProfile.mockRejectedValueOnce(new Error('network'));
        const store = useAuthStore();
        store.setToken('jwt');
        const result = await store.fetchUser();
        expect(result).toBeNull();
    });

    it('fetchUser hydrates user on success', async () => {
        const profile = {
            id: 7,
            name: 'Loaded',
            email: 'l@l.com',
            role: 'customer' as const,
            phone: null,
            active: true,
            email_verified_at: null,
            created_at: '2020-01-01T00:00:00.000000Z',
            updated_at: '2020-01-01T00:00:00.000000Z',
        };
        getProfile.mockResolvedValueOnce(profile);
        const store = useAuthStore();
        store.setToken('jwt');
        const result = await store.fetchUser();
        expect(result).toEqual(profile);
        expect(store.user).toEqual(profile);
    });
});
