import { useApi } from '~/composables/useApi';
import type { AuthResponse, LoginPayload, RegisterPayload, UserProfile } from '~/types/auth';

/**
 * Typed auth HTTP helpers. Token attachment and global error handling live in {@link useApi}.
 *
 * Core endpoints: login, register, password recovery, email verification (signed URL), profile.
 */
export function useAuthApi() {
    const { apiFetch } = useApi();

    async function login(payload: LoginPayload): Promise<AuthResponse> {
        const response = await apiFetch<{ success: boolean; data: AuthResponse }>(
            '/v1/auth/login',
            {
                method: 'POST',
                body: payload,
            }
        );
        return response.data;
    }

    async function register(payload: RegisterPayload): Promise<AuthResponse> {
        const response = await apiFetch<{ success: boolean; data: AuthResponse }>(
            '/v1/auth/register',
            {
                method: 'POST',
                body: payload,
            }
        );
        return response.data;
    }

    async function forgotPassword(email: string): Promise<void> {
        await apiFetch('/v1/auth/forgot-password', {
            method: 'POST',
            body: { email },
        });
    }

    async function resetPassword(body: {
        email: string;
        token: string;
        password: string;
        password_confirmation: string;
    }): Promise<void> {
        await apiFetch('/v1/auth/reset-password', {
            method: 'POST',
            body,
        });
    }

    /** Laravel signed email verification link (GET). */
    async function verifyEmail(
        id: string,
        hash: string,
        query?: Record<string, string | undefined>
    ): Promise<void> {
        const params = new URLSearchParams();
        if (query) {
            for (const [key, value] of Object.entries(query)) {
                if (value !== undefined) {
                    params.set(key, value);
                }
            }
        }
        const qs = params.toString();
        const path = `/v1/auth/email/verify/${encodeURIComponent(id)}/${encodeURIComponent(hash)}${
            qs ? `?${qs}` : ''
        }`;
        await apiFetch(path, { method: 'GET' });
    }

    async function getProfile(): Promise<UserProfile> {
        const response = await apiFetch<{ success: boolean; data: UserProfile }>(
            '/v1/auth/profile'
        );
        return response.data;
    }

    async function updateProfile(payload: {
        name?: string;
        phone?: string | null;
    }): Promise<UserProfile> {
        const response = await apiFetch<{ success: boolean; data: UserProfile }>(
            '/v1/auth/profile',
            {
                method: 'PUT',
                body: payload,
            }
        );
        return response.data;
    }

    async function resendEmailVerification(): Promise<void> {
        await apiFetch('/v1/auth/email/resend', { method: 'POST' });
    }

    async function logout(): Promise<void> {
        await apiFetch('/v1/auth/logout', { method: 'POST' });
    }

    return {
        login,
        register,
        forgotPassword,
        resetPassword,
        verifyEmail,
        getProfile,
        updateProfile,
        resendEmailVerification,
        logout,
    };
}
