<script setup lang="ts">
    definePageMeta({
        layout: 'auth',
    });

    const { apiFetch } = useApi();
    const { user, isEmailVerified } = useAuth();

    const loading = ref(false);
    const success = ref(false);
    const error = ref<string | null>(null);
    const cooldown = ref(0);

    let timer: ReturnType<typeof setInterval> | null = null;

    async function resendVerification() {
        if (cooldown.value > 0) return;

        loading.value = true;
        error.value = null;

        try {
            await apiFetch('/v1/auth/email/resend', { method: 'POST' });
            success.value = true;
            cooldown.value = 60;

            timer = setInterval(() => {
                cooldown.value--;
                if (cooldown.value <= 0 && timer) {
                    clearInterval(timer);
                    timer = null;
                }
            }, 1000);
        } catch (e: unknown) {
            const err = e as { data?: { error?: { message?: string } } };
            error.value = err?.data?.error?.message || 'حدث خطأ غير متوقع';
        } finally {
            loading.value = false;
        }
    }

    onUnmounted(() => {
        if (timer) clearInterval(timer);
    });
</script>

<template>
    <div class="text-center">
        <div class="mb-4 flex justify-center">
            <UIcon name="i-heroicons-envelope" class="h-12 w-12 text-[#0072f5]" />
        </div>

        <h2 class="mb-2 text-xl font-semibold text-[#171717] dark:text-white">
            {{ $t('auth.verify_email') }}
        </h2>

        <template v-if="isEmailVerified">
            <UAlert
                color="green"
                variant="subtle"
                :title="$t('auth.email_verified')"
                class="mb-4"
            />
            <UButton block size="lg" @click="navigateTo('/ar/dashboard')">
                {{ $t('auth.go_to_dashboard') }}
            </UButton>
        </template>

        <template v-else>
            <p class="mb-6 text-sm text-[#666666]">
                {{ $t('auth.verify_email_description') }}
                <span v-if="user" class="font-medium text-[#171717] dark:text-white">
                    {{ user.email }}
                </span>
            </p>

            <UAlert
                v-if="success"
                color="green"
                variant="subtle"
                :title="$t('auth.verification_sent')"
                class="mb-4"
            />

            <UAlert v-if="error" color="red" variant="subtle" :title="error" class="mb-4" />

            <UButton
                block
                size="lg"
                :loading="loading"
                :disabled="cooldown > 0"
                @click="resendVerification"
            >
                <template v-if="cooldown > 0">
                    {{ $t('auth.resend_in', { seconds: cooldown }) }}
                </template>
                <template v-else>
                    {{ $t('auth.resend_verification') }}
                </template>
            </UButton>

            <div class="mt-6">
                <NuxtLink
                    to="/ar/auth/login"
                    class="text-sm font-medium text-[#171717] hover:underline dark:text-white"
                >
                    {{ $t('auth.back_to_login') }}
                </NuxtLink>
            </div>
        </template>
    </div>
</template>
