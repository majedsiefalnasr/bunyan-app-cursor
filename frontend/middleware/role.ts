import type { UserRole } from '~/types/auth';

export default defineNuxtRouteMiddleware((to) => {
    const requiredRoles = to.meta.roles as UserRole[] | undefined;
    if (!requiredRoles || requiredRoles.length === 0) return;

    const auth = useAuthStore();
    const userRole = auth.userRole;

    if (!userRole || !requiredRoles.includes(userRole)) {
        return navigateTo('/ar/dashboard');
    }
});
