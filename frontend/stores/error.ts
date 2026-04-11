import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';

export interface ClientError {
    code: string;
    message: string;
    details?: Record<string, unknown> | null;
    timestamp: number;
}

export const useErrorStore = defineStore('error', () => {
    const errors = ref<ClientError[]>([]);
    const lastError = ref<ClientError | null>(null);
    let clearTimer: ReturnType<typeof setTimeout> | null = null;

    const hasErrors = computed(() => errors.value.length > 0);

    function addError(error: ClientError) {
        errors.value.push(error);
        lastError.value = error;

        if (clearTimer) {
            clearTimeout(clearTimer);
        }
        clearTimer = setTimeout(() => {
            clearErrors();
        }, 30_000);
    }

    function clearErrors() {
        errors.value = [];
        lastError.value = null;
        if (clearTimer) {
            clearTimeout(clearTimer);
            clearTimer = null;
        }
    }

    watch(lastError, () => {
        //
    });

    return { errors, lastError, hasErrors, addError, clearErrors };
});
