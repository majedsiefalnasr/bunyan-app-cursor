export default defineNuxtRouteMiddleware((to) => {
    if (to.meta.requiresAuth !== true) return;

    const auth = useAuthStore();
    if (!auth.token) {
        return navigateTo('/ar/auth/login');
    }
});
