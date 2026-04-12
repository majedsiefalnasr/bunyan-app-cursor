<script setup lang="ts">
    import type { NuxtUiFormSubmitEvent } from '~/types/nuxt-ui-form';
    import { loginSchema } from '~/schemas/auth';
    import type { LoginFormValues } from '~/schemas/auth';

    definePageMeta({
        layout: 'auth',
    });

    const { login } = useAuth();
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

    async function onSubmit(event: NuxtUiFormSubmitEvent<LoginFormValues>) {
        loading.value = true;
        error.value = null;

        try {
            await login(event.data);
        } catch (e: unknown) {
            const err = e as { data?: { error?: { message?: string } } };
            error.value = err?.data?.error?.message || 'حدث خطأ غير متوقع';
        } finally {
            loading.value = false;
        }
    }
</script>

<template>
    <AuthCard :title="$t('auth.login')">
        <UAlert
            v-if="error"
            color="red"
            variant="subtle"
            role="alert"
            :title="error"
            class="mb-4"
            data-testid="auth-error-alert"
            @close="error = null"
        />

        <UForm :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
            <UFormField :label="$t('auth.email')" name="email">
                <UInput
                    v-model="state.email"
                    type="email"
                    :placeholder="$t('auth.email_placeholder')"
                    icon="i-heroicons-envelope"
                    size="lg"
                />
            </UFormField>

            <UFormField :label="$t('auth.password')" name="password">
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
            </UFormField>

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
