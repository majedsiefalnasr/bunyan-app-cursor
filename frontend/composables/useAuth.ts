import { computed } from 'vue';
import type { UserRole } from '~/types/auth';
import { useAuthStore } from '~/stores/auth';

export function useAuth() {
    const store = useAuthStore();

    const user = computed(() => store.user);
    const role = computed<UserRole | null>(() => store.user?.role ?? null);
    const isAuthenticated = computed(() => store.isAuthenticated);

    async function logout() {
        store.logout();
        await navigateTo('/ar/auth/login');
    }

    function hasRole(...roles: UserRole[]): boolean {
        return role.value !== null && roles.includes(role.value);
    }

    return { user, role, isAuthenticated, logout, hasRole };
}
