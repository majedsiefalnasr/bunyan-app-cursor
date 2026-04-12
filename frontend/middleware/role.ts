import type { UserRole } from '~/types/auth';

export default defineNuxtRouteMiddleware((to) => {
    const requiredRoles = to.meta.roles as UserRole[] | undefined;
    if (!requiredRoles || requiredRoles.length === 0) return;

    const auth = useAuthStore();
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
