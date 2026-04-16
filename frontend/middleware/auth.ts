export default defineNuxtRouteMiddleware((to) => {
    if (to.meta.requiresAuth !== true) return;

    const localePath = useLocalePath();
    // Read cookie directly so SSR + first-load redirects work even before Pinia hydrates.
    const tokenCookie = useCookie<string | null>('auth_token');
    const token = tokenCookie.value;

    if (!token) {
        return navigateTo({
            path: localePath('/auth/login'),
            query: { redirect: to.fullPath },
        });
    }
});
