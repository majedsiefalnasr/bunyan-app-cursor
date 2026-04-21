import type { ErrorPayload } from '~/types/errors';

export function useErrorNotification() {
    const toast = useToast();
    // `useI18n()` must be called within a component `setup()`. This composable can be used from
    // `$fetch` hooks / stores, so we rely on Nuxt-injected i18n instead.
    const nuxtApp = useNuxtApp() as unknown;
    const composer = (nuxtApp as { $i18n?: unknown }).$i18n as
        | { t?: (...args: unknown[]) => unknown; te?: (...args: unknown[]) => unknown }
        | undefined;
    const t = (key: string) => {
        if (typeof composer?.t !== 'function') {
            return key;
        }
        const out = composer.t(key);
        return typeof out === 'string' ? out : String(out ?? key);
    };
    const te = (key: string) => {
        if (typeof composer?.te !== 'function') {
            return false;
        }
        const out = composer.te(key);
        return Boolean(out);
    };

    function severityFor(statusCode?: number, code?: string): 'error' | 'warning' {
        if (statusCode !== undefined && statusCode >= 500) {
            return 'error';
        }
        const hard: string[] = ['SERVER_ERROR', 'SERVICE_UNAVAILABLE', 'PAYMENT_FAILED'];
        if (code && hard.includes(code)) {
            return 'error';
        }
        return 'warning';
    }

    function showErrorNotification(payload: ErrorPayload) {
        // Toasts are client-only UI — never add them during SSR to prevent hydration mismatches.
        if (import.meta.server) return;

        const sev = severityFor(payload.statusCode, payload.code);
        const key = `errors.codes.${payload.code}.message`;
        const localized = te(key) ? t(key) : payload.message;
        const title = localized || String(payload.code);

        toast.add({
            title,
            description: '',
            color: sev === 'error' ? 'error' : 'warning',
            duration: sev === 'error' ? 8000 : 5000,
        });
    }

    return { showErrorNotification };
}
