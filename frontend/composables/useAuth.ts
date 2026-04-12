import { computed } from 'vue';
import type { LoginPayload, RegisterPayload, UserRole } from '~/types/auth';
import { useAuthStore } from '~/stores/auth';

export function useAuth() {
    const store = useAuthStore();

    const user = computed(() => store.user);
    const role = computed<UserRole | null>(() => store.user?.role ?? null);
    const isAuthenticated = computed(() => store.isAuthenticated);
    const isEmailVerified = computed(() => !!store.user?.email_verified_at);

    async function login(payload: LoginPayload) {
        const result = await store.login(payload);
        await navigateTo('/ar/dashboard');
        return result;
    }

    async function register(payload: RegisterPayload) {
        const result = await store.register(payload);
        await navigateTo('/ar/auth/verify-email');
        return result;
    }

    async function logout() {
        await store.logout();
        await navigateTo('/ar/auth/login');
    }

    function hasRole(...roles: UserRole[]): boolean {
        return role.value !== null && roles.includes(role.value);
    }

    return { user, role, isAuthenticated, isEmailVerified, login, register, logout, hasRole };
}
