import { computed } from 'vue';
import type { LoginPayload, RegisterPayload, UserRole } from '~/types/auth';
import { useAuthStore } from '~/stores/auth';

export function useAuth() {
    const store = useAuthStore();
    const localePath = useLocalePath();

    const user = computed(() => store.user);
    const role = computed<UserRole | null>(() => store.user?.role ?? null);
    const permissions = computed(() => store.permissions);
    const isAuthenticated = computed(() => store.isAuthenticated);
    const isEmailVerified = computed(() => !!store.user?.email_verified_at);

    async function login(payload: LoginPayload) {
        const result = await store.login(payload);
        await navigateTo(localePath('/dashboard'));
        return result;
    }

    async function register(
        payload: RegisterPayload,
        options?: { skipPostRegisterNavigation?: boolean }
    ) {
        const result = await store.register(payload);
        if (!options?.skipPostRegisterNavigation) {
            await navigateTo(localePath('/auth/verify-email'));
        }
        return result;
    }

    async function logout() {
        await store.logout();
        await navigateTo(localePath('/auth/login'));
    }

    async function updateProfile(payload: { name?: string; phone?: string | null }) {
        return store.updateProfile(payload);
    }

    function hasRole(...roles: UserRole[]): boolean {
        return role.value !== null && roles.includes(role.value);
    }

    function hasPermission(name: string): boolean {
        return permissions.value.includes(name);
    }

    function hasAnyPermission(names: string[]): boolean {
        return names.some((n) => permissions.value.includes(n));
    }

    return {
        user,
        role,
        permissions,
        isAuthenticated,
        isEmailVerified,
        login,
        register,
        logout,
        updateProfile,
        hasRole,
        hasPermission,
        hasAnyPermission,
    };
}
