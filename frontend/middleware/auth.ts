export default defineNuxtRouteMiddleware((to) => {
    if (to.meta.requiresAuth !== true) return;

    const auth = useAuthStore();
    const localePath = useLocalePath();

    if (!auth.token) {
        return navigateTo({
            path: localePath('/auth/login'),
            query: { redirect: to.fullPath },
        });
    }
});
