import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useAuthStore } from '~/stores/auth';
import type { UserProfile } from '~/types/auth';

/**
 * Profile-focused Pinia store (T001). Delegates HTTP to `useAuthStore` / API layer
 * and keeps a local `profile` ref for components that prefer explicit user-store APIs.
 */
export const useUserStore = defineStore('user', () => {
    const auth = useAuthStore();

    const profile = ref<UserProfile | null>(null);
    const loading = ref(false);
    const lastError = ref<string | null>(null);

    const email = computed(() => profile.value?.email ?? auth.user?.email ?? null);

    watch(
        () => auth.user,
        (u) => {
            profile.value = u ? { ...u } : null;
        },
        { immediate: true, deep: true }
    );

    async function fetchProfile(): Promise<UserProfile | null> {
        loading.value = true;
        lastError.value = null;
        try {
            const u = await auth.fetchUser();
            profile.value = u ? { ...u } : null;
            return u;
        } finally {
            loading.value = false;
        }
    }

    async function updateProfile(payload: {
        name?: string;
        phone?: string | null;
    }): Promise<UserProfile> {
        loading.value = true;
        lastError.value = null;
        try {
            const u = await auth.updateProfile(payload);
            profile.value = { ...u };
            return u;
        } catch (e: unknown) {
            const err = e as { data?: { error?: { message?: string } } };
            lastError.value = err?.data?.error?.message ?? null;
            throw e;
        } finally {
            loading.value = false;
        }
    }

    function clearLocalProfile() {
        profile.value = null;
        lastError.value = null;
    }

    return {
        profile,
        loading,
        lastError,
        email,
        fetchProfile,
        updateProfile,
        clearLocalProfile,
    };
});
