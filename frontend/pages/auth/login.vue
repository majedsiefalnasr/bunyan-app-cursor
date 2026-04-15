<script setup lang="ts">
    import type { NuxtUiFormSubmitEvent } from '~/types/nuxt-ui-form';
    import { loginSchema } from '~/schemas/auth';
    import type { LoginFormValues } from '~/schemas/auth';

    definePageMeta({
        layout: 'auth',
    });

    const authStore = useAuthStore();
    const localePath = useLocalePath();

    const schema = loginSchema;

    const state = reactive<LoginFormValues>({
        email: '',
        password: '',
    });

    const loading = ref(false);
    const error = ref<string | null>(null);
    const showPassword = ref(false);
    const rememberMe = ref(false);

    /** Normalize `$fetch` / ofetch error shapes (body on `data` or `response._data`). */
    function messageFromAuthCatch(err: unknown): string | null {
        if (!err || typeof err !== 'object') return null;
        const o = err as Record<string, unknown>;
        const fromData = (payload: unknown): string | null => {
            if (!payload || typeof payload !== 'object') return null;
            const p = payload as { error?: { message?: string } };
            return p.error?.message ?? null;
        };
        const direct = fromData(o.data);
        if (direct) return direct;
        const res = o.response;
        if (res && typeof res === 'object' && '_data' in res) {
            return fromData((res as { _data?: unknown })._data);
        }
        return null;
    }

    async function onSubmit(event: NuxtUiFormSubmitEvent<LoginFormValues>) {
        loading.value = true;
        error.value = null;

        try {
            const payload = event.data ?? {
                email: state.email,
                password: state.password,
            };
            await authStore.login(payload);
            await navigateTo(localePath('/dashboard'));
        } catch (e: unknown) {
            error.value = messageFromAuthCatch(e) || 'حدث خطأ غير متوقع';
        } finally {
            loading.value = false;
        }
    }
</script>

<template>
    <AuthCard :title="$t('auth.login')">
        <div v-if="error" role="alert" data-testid="auth-error-alert" class="mb-4">
            <UAlert color="red" variant="subtle" :title="error" @close="error = null" />
        </div>

        <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
            <UFormGroup :label="$t('auth.email')" name="email">
                <UInput
                    v-model="state.email"
                    type="email"
                    :placeholder="$t('auth.email_placeholder')"
                    icon="i-heroicons-envelope"
                    size="lg"
                />
            </UFormGroup>

            <UFormGroup :label="$t('auth.password')" name="password">
                <div class="flex items-stretch gap-2">
                    <UInput
                        v-model="state.password"
                        :type="showPassword ? 'text' : 'password'"
                        :placeholder="$t('auth.password_placeholder')"
                        icon="i-heroicons-lock-closed"
                        size="lg"
                        class="min-w-0 flex-1"
                    />
                    <UButton
                        color="gray"
                        variant="outline"
                        type="button"
                        size="lg"
                        :icon="showPassword ? 'i-heroicons-eye-slash' : 'i-heroicons-eye'"
                        :aria-label="
                            showPassword ? $t('auth.hide_password') : $t('auth.show_password')
                        "
                        @click="showPassword = !showPassword"
                    />
                </div>
            </UFormGroup>

            <div class="flex items-center justify-between gap-3">
                <UCheckbox v-model="rememberMe" :label="$t('auth.remember_me')" />
                <NuxtLink
                    :to="localePath('/auth/forgot-password')"
                    class="text-sm text-[#0072f5] hover:underline"
                >
                    {{ $t('auth.forgot_password') }}
                </NuxtLink>
            </div>

            <UButton type="submit" block size="lg" :loading="loading">
                {{ $t('auth.login') }}
            </UButton>
        </UForm>

        <div class="mt-6 text-center text-sm text-[#666666]">
            <p>
                {{ $t('auth.no_account') }}
                <NuxtLink
                    :to="localePath('/auth/register')"
                    class="font-medium text-[#171717] hover:underline dark:text-white"
                >
                    {{ $t('auth.register') }}
                </NuxtLink>
            </p>
        </div>
    </AuthCard>
</template>
