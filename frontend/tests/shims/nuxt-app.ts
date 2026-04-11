import { ref, type Ref } from 'vue';

const cookieRegistry = new Map<string, Ref<unknown>>();

/**
 * Test shim for Nuxt `useCookie` (Vitest has no auto-imports / SSR cookie layer).
 * Cleared between tests via `resetNuxtAppTestShims`.
 */
export function useCookie<T = string | null>(
    name: string,
    _opts?: Record<string, unknown>
): Ref<T | null> {
    let entry = cookieRegistry.get(name);
    if (!entry) {
        entry = ref<T | null>(null);
        cookieRegistry.set(name, entry);
    }
    return entry as Ref<T | null>;
}

/** Reset cookie refs so unit tests do not leak auth_token across files. */
export function resetNuxtAppTestShims(): void {
    cookieRegistry.clear();
}

export function reloadNuxtApp() {
    if (typeof window !== 'undefined') {
        window.location.reload();
    }
}
