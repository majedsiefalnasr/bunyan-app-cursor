import type { ErrorPayload } from '~/types/errors';

export function useErrorNotification() {
    const toast = useToast();
    const { t, te } = useI18n();

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
        const sev = severityFor(payload.statusCode, payload.code);
        const title = String(payload.code);
        const key = `errors.codes.${payload.code}.message`;
        const localized = te(key) ? t(key) : payload.message;

        toast.add({
            title,
            description: localized,
            color: sev === 'error' ? 'red' : 'yellow',
            timeout: sev === 'error' ? 8000 : 5000,
        });
    }

    return { showErrorNotification };
}
