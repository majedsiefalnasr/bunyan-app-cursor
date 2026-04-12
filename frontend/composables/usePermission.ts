import { computed } from 'vue';
import { useAuthStore } from '~/stores/auth';

export function usePermission() {
    const auth = useAuthStore();
    const permissions = computed(() => auth.permissions);

    function hasPermission(name: string): boolean {
        return permissions.value.includes(name);
    }

    function hasAnyPermission(names: string[]): boolean {
        return names.some((n) => permissions.value.includes(n));
    }

    function hasAllPermissions(names: string[]): boolean {
        return names.every((n) => permissions.value.includes(n));
    }

    return { permissions, hasPermission, hasAnyPermission, hasAllPermissions };
}
