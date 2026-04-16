import { useErrorNotification } from '~/composables/useErrorNotification';
import { useAuthStore } from '~/stores/auth';
import { useErrorStore } from '~/stores/error';

function generateCorrelationId(): string {
    return `req_${Date.now()}_${Math.random().toString(16).slice(2, 10)}`;
}

export function useApi() {
    const config = useRuntimeConfig();
    const auth = useAuthStore();
    const errorStore = useErrorStore();
    const { showErrorNotification } = useErrorNotification();
    // `useLocalePath()` (nuxt-i18n) must be called within component `setup()`. This composable is
    // also used from stores, so we use the Nuxt-injected helper instead.
    const nuxtApp = useNuxtApp() as unknown;
    const localePath: (path: string) => string = (() => {
        const candidate = (nuxtApp as { $localePath?: unknown }).$localePath;
        return typeof candidate === 'function' ? (candidate as (path: string) => string) : (p) => p;
    })();

    const rawBaseUrl = (config.public.apiBaseUrl || '').toString().replace(/\/$/, '');
    const baseURL = rawBaseUrl
        ? rawBaseUrl.endsWith('/api')
            ? rawBaseUrl
            : `${rawBaseUrl}/api`
        : '';

    const apiFetch = $fetch.create({
        baseURL,
        headers: { Accept: 'application/json' },
        onRequest({ options }) {
            const headers = new Headers(options.headers as HeadersInit | undefined);
            const token = auth.token;
            if (token) {
                headers.set('Authorization', `Bearer ${token}`);
            }

            const app = nuxtApp as { $i18n?: unknown; $locale?: unknown };
            const i18n = app.$i18n as { locale?: unknown } | undefined;
            const localeRaw = i18n?.locale;
            const localeValue =
                localeRaw && typeof localeRaw === 'object' && 'value' in localeRaw
                    ? (localeRaw as { value?: unknown }).value
                    : undefined;
            const currentLocale = localeValue ?? localeRaw ?? app.$locale ?? 'ar';
            headers.set('Accept-Language', String(currentLocale));

            headers.set('X-Correlation-ID', generateCorrelationId());
            options.headers = headers;
        },
        async onResponseError({ response, request: _request }) {
            const data = (response._data || {}) as {
                error?: {
                    code?: string;
                    message?: string;
                    details?: Record<string, unknown> | null;
                };
            };
            const error = data.error || {};
            const errorCode = (error.code || 'SERVER_ERROR') as string;
            const message = error.message || '';
            const statusCode = response.status;

            errorStore.addError({
                code: errorCode,
                message,
                details: error.details ?? null,
                timestamp: Date.now(),
            });

            if (statusCode === 401) {
                await auth.logout();
                if (errorCode !== 'AUTH_TOKEN_EXPIRED') {
                    await navigateTo(localePath('/auth/login'));
                }
            } else if (statusCode === 403 && errorCode === 'RBAC_ROLE_DENIED') {
                await navigateTo(localePath('/dashboard'));
            }

            showErrorNotification({
                code: errorCode,
                message,
                details: error.details,
                statusCode,
            });
        },
    });

    return { apiFetch };
}
