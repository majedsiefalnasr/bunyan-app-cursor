import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useAuthApi } from '~/composables/useAuthApi';

const apiFetch = vi.fn();

vi.mock('~/composables/useApi', () => ({
    useApi: () => ({ apiFetch }),
}));

describe('useAuthApi', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    it('login posts to /v1/auth/login and returns data', async () => {
        const payload = { email: 'u@example.com', password: 'password1' };
        const data = {
            token: 'tok',
            user: {
                id: 1,
                name: 'U',
                email: 'u@example.com',
                role: 'customer' as const,
                phone: null,
                active: true,
                email_verified_at: null,
                created_at: '2020-01-01T00:00:00.000000Z',
                updated_at: '2020-01-01T00:00:00.000000Z',
            },
        };
        apiFetch.mockResolvedValueOnce({ success: true, data });

        const authApi = useAuthApi();
        const result = await authApi.login(payload);

        expect(apiFetch).toHaveBeenCalledWith('/v1/auth/login', {
            method: 'POST',
            body: payload,
        });
        expect(result).toEqual(data);
    });

    it('forgotPassword posts email', async () => {
        apiFetch.mockResolvedValueOnce({ success: true, data: null });
        const authApi = useAuthApi();
        await authApi.forgotPassword('a@b.com');
        expect(apiFetch).toHaveBeenCalledWith('/v1/auth/forgot-password', {
            method: 'POST',
            body: { email: 'a@b.com' },
        });
    });

    it('verifyEmail builds signed GET path', async () => {
        apiFetch.mockResolvedValueOnce({ success: true, data: null });
        const authApi = useAuthApi();
        await authApi.verifyEmail('5', 'abc', { expires: '1', signature: 'sig' });
        expect(apiFetch).toHaveBeenCalledWith(
            '/v1/auth/email/verify/5/abc?expires=1&signature=sig',
            { method: 'GET' }
        );
    });

    it('getProfile uses GET /v1/auth/profile', async () => {
        const profile = {
            id: 2,
            name: 'P',
            email: 'p@example.com',
            role: 'contractor' as const,
            phone: null,
            active: true,
            email_verified_at: null,
            created_at: '2020-01-01T00:00:00.000000Z',
            updated_at: '2020-01-01T00:00:00.000000Z',
        };
        apiFetch.mockResolvedValueOnce({ success: true, data: profile });
        const authApi = useAuthApi();
        const result = await authApi.getProfile();
        expect(apiFetch).toHaveBeenCalledWith('/v1/auth/profile');
        expect(result).toEqual(profile);
    });

    it('resendEmailVerification posts resend endpoint', async () => {
        apiFetch.mockResolvedValueOnce({ success: true, data: null });
        const authApi = useAuthApi();
        await authApi.resendEmailVerification();
        expect(apiFetch).toHaveBeenCalledWith('/v1/auth/email/resend', { method: 'POST' });
    });
});
