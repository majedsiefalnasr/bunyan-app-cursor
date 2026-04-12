import { useCookie } from '#app';
import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useApi } from '~/composables/useApi';
import type {
    AuthResponse,
    LoginPayload,
    RegisterPayload,
    UserProfile,
    UserRole,
} from '~/types/auth';

export const useAuthStore = defineStore('auth', () => {
    const tokenCookie = useCookie<string | null>('auth_token', {
        maxAge: 60 * 60 * 24,
        sameSite: 'lax',
        secure: process.env.NODE_ENV === 'production',
    });

    const token = ref<string | null>(tokenCookie.value ?? null);
    const user = ref<UserProfile | null>(null);
    const permissions = ref<string[]>([]);

    const isAuthenticated = computed(() => !!token.value);
    const userRole = computed<UserRole | null>(() => user.value?.role ?? null);

    watch(token, (newToken) => {
        tokenCookie.value = newToken;
    });

    function setToken(value: string | null) {
        token.value = value;
    }

    function setUser(profile: UserProfile | null) {
        user.value = profile;
    }

    async function login(payload: LoginPayload): Promise<AuthResponse> {
        const { apiFetch } = useApi();
        const response = await apiFetch<{ success: boolean; data: AuthResponse }>(
            '/v1/auth/login',
            {
                method: 'POST',
                body: payload,
            }
        );

        token.value = response.data.token;
        user.value = response.data.user;
        permissions.value = response.data.user.permissions ?? [];

        return response.data;
    }

    async function register(payload: RegisterPayload): Promise<AuthResponse> {
        const { apiFetch } = useApi();
        const response = await apiFetch<{ success: boolean; data: AuthResponse }>(
            '/v1/auth/register',
            {
                method: 'POST',
                body: payload,
            }
        );

        token.value = response.data.token;
        user.value = response.data.user;
        permissions.value = response.data.user.permissions ?? [];

        return response.data;
    }

    async function fetchUser(): Promise<UserProfile | null> {
        if (!token.value) return null;

        try {
            const { apiFetch } = useApi();
            const response = await apiFetch<{ success: boolean; data: UserProfile }>(
                '/v1/auth/profile'
            );
            user.value = response.data;
            permissions.value = response.data.permissions ?? [];
            return response.data;
        } catch {
            return null;
        }
    }

    async function logout(): Promise<void> {
        if (token.value) {
            try {
                const { apiFetch } = useApi();
                await apiFetch('/v1/auth/logout', { method: 'POST' });
            } catch {
                // Clear state regardless of API errors
            }
        }
        token.value = null;
        user.value = null;
        permissions.value = [];
    }

    return {
        token,
        user,
        permissions,
        isAuthenticated,
        userRole,
        setToken,
        setUser,
        login,
        register,
        fetchUser,
        logout,
    };
});
