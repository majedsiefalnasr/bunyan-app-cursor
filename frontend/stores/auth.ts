import { useCookie } from '#app';
import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useAuthApi } from '~/composables/useAuthApi';
import type { LoginPayload, RegisterPayload, UserProfile, UserRole } from '~/types/auth';

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
        permissions.value = profile?.permissions ?? [];
    }

    async function login(payload: LoginPayload) {
        const authApi = useAuthApi();
        const data = await authApi.login(payload);
        token.value = data.token;
        user.value = data.user;
        permissions.value = data.user.permissions ?? [];
        return data;
    }

    async function register(payload: RegisterPayload) {
        const authApi = useAuthApi();
        const data = await authApi.register(payload);
        token.value = data.token;
        user.value = data.user;
        permissions.value = data.user.permissions ?? [];
        return data;
    }

    async function fetchUser(): Promise<UserProfile | null> {
        if (!token.value) return null;

        try {
            const authApi = useAuthApi();
            const profile = await authApi.getProfile();
            user.value = profile;
            permissions.value = profile.permissions ?? [];
            return profile;
        } catch {
            return null;
        }
    }

    async function updateProfile(payload: {
        name?: string;
        phone?: string | null;
    }): Promise<UserProfile> {
        const authApi = useAuthApi();
        const profile = await authApi.updateProfile(payload);
        user.value = profile;
        permissions.value = profile.permissions ?? [];
        return profile;
    }

    async function logout(): Promise<void> {
        if (token.value) {
            try {
                const authApi = useAuthApi();
                await authApi.logout();
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
        updateProfile,
        logout,
    };
});
