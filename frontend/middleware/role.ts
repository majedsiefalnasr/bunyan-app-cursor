import type { UserRole } from '~/types/auth';

export default defineNuxtRouteMiddleware(async (to) => {
    const requiredRoles = to.meta.roles as UserRole[] | undefined;
    if (!requiredRoles || requiredRoles.length === 0) return;

    const auth = useAuthStore();
    // If we have a token but no hydrated user yet, fetch profile once so RBAC works on first navigation.
    // Without this, role-protected routes would always redirect on hard refresh / new session.
    if (auth.token && !auth.user) {
        try {
            await auth.fetchUser();
        } catch {
            // If profile load fails, fall through to redirect logic below.
        }
    }

    const userRole = auth.userRole;

    if (!userRole || !requiredRoles.includes(userRole)) {
        const toast = useToast();
        toast.add({
            title: 'غير مصرح',
            description: 'ليس لديك الصلاحية للوصول لهذه الصفحة',
            color: 'red',
            icon: 'i-heroicons-exclamation-triangle',
        });

        return navigateTo('/ar/dashboard');
    }
});
