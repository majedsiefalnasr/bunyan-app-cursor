import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import type { UserProfile, UserRole } from '~/types/auth';

export const useAuthStore = defineStore('auth', () => {
    const token = ref<string | null>(null);
    const user = ref<UserProfile | null>(null);

    const isAuthenticated = computed(() => !!token.value);
    const userRole = computed<UserRole | null>(() => user.value?.role ?? null);

    function setToken(value: string | null) {
        token.value = value;
    }

    function setUser(profile: UserProfile | null) {
        user.value = profile;
    }

    function logout() {
        token.value = null;
        user.value = null;
    }

    return { token, user, isAuthenticated, userRole, setToken, setUser, logout };
});
