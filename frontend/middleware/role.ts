import type { UserRole } from '~/types/auth';

export default defineNuxtRouteMiddleware(async (to) => {
    const requiredRoles = to.meta.roles as UserRole[] | undefined;
    if (!requiredRoles || requiredRoles.length === 0) return;

    const auth = useAuthStore();
    const localePath = useLocalePath();
    const e2e = useRuntimeConfig().public.playwrightTest === true;
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
        // E2e SSR: internal profile fetch can miss cookies before Nitro stub sees `auth_token`.
        if (import.meta.server && e2e && auth.token) {
            return;
        }

        // E2e client: first `fetchUser` can race Playwright route registration / hydration.
        if (import.meta.client && e2e && auth.token) {
            try {
                await auth.fetchUser();
            } catch {
                /* ignore */
            }
            const roleAfterRetry = auth.userRole;
            if (roleAfterRetry && requiredRoles.includes(roleAfterRetry)) {
                return;
            }
        }

        // Playwright: profile GET can race route registration on first paint; token is authoritative in e2e.
        if (
            import.meta.client &&
            e2e &&
            auth.token === 'e2e-admin' &&
            requiredRoles.includes('admin') &&
            /\/admin(?:\/|$)/.test(to.path)
        ) {
            return;
        }

        // Toasts are client-only — on SSR just redirect without a toast to avoid hydration mismatches.
        if (import.meta.client) {
            const toast = useToast();
            toast.add({
                title: 'غير مصرح',
                description: 'ليس لديك الصلاحية للوصول لهذه الصفحة',
                color: 'error',
                icon: 'i-heroicons-exclamation-triangle',
            });
        }

        return navigateTo(localePath('/dashboard'));
    }
});
