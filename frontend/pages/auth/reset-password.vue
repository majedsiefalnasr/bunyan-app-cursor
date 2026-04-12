<script setup lang="ts">
    import type { NuxtUiFormSubmitEvent } from '~/types/nuxt-ui-form';
    import { resetPasswordSchema } from '~/schemas/auth';
    import type { ResetPasswordFormValues } from '~/schemas/auth';

    definePageMeta({
        layout: 'auth',
    });

    const route = useRoute();
    const { apiFetch } = useApi();
    const localePath = useLocalePath();

    const schema = resetPasswordSchema;

    type ResetSchema = ResetPasswordFormValues;

    const state = reactive<Partial<ResetSchema>>({
        password: '',
        password_confirmation: '',
    });

    const loading = ref(false);
    const success = ref(false);
    const error = ref<string | null>(null);

    const token = computed(() => (route.query.token as string) || '');
    const email = computed(() => (route.query.email as string) || '');

    async function onSubmit(event: NuxtUiFormSubmitEvent<ResetSchema>) {
        loading.value = true;
        error.value = null;

        try {
            await apiFetch('/v1/auth/reset-password', {
                method: 'POST',
                body: {
                    token: token.value,
                    email: email.value,
                    password: event.data.password,
                    password_confirmation: event.data.password_confirmation,
                },
            });
            success.value = true;
            setTimeout(() => {
                void navigateTo(localePath('/auth/login'));
            }, 3000);
        } catch (e: unknown) {
            const err = e as { data?: { error?: { message?: string } } };
            error.value = err?.data?.error?.message || 'حدث خطأ غير متوقع';
        } finally {
            loading.value = false;
        }
    }
</script>

<template>
    <AuthCard :title="$t('auth.reset_password')">
        <UAlert
            v-if="success"
            color="green"
            variant="subtle"
            :title="$t('auth.password_reset_success')"
            :description="$t('auth.redirecting_to_login')"
            class="mb-4"
        />

        <UAlert
            v-if="error"
            color="red"
            variant="subtle"
            :title="error"
            class="mb-4"
            @close="error = null"
        />

        <UForm v-if="!success" :schema="schema" :state="state" class="space-y-4" @submit="onSubmit">
            <UFormField :label="$t('auth.new_password')" name="password">
                <UInput
                    v-model="state.password"
                    type="password"
                    :placeholder="$t('auth.password_placeholder')"
                    icon="i-heroicons-lock-closed"
                    size="lg"
                />
            </UFormField>

            <PasswordStrength :password="state.password || ''" />

            <UFormField :label="$t('auth.password_confirmation')" name="password_confirmation">
                <UInput
                    v-model="state.password_confirmation"
                    type="password"
                    :placeholder="$t('auth.password_confirmation_placeholder')"
                    icon="i-heroicons-lock-closed"
                    size="lg"
                />
            </UFormField>

            <UButton type="submit" block size="lg" :loading="loading">
                {{ $t('auth.reset_password') }}
            </UButton>
        </UForm>

        <div class="mt-6 text-center text-sm text-[#666666]">
            <NuxtLink
                :to="localePath('/auth/login')"
                class="font-medium text-[#171717] hover:underline dark:text-white"
            >
                {{ $t('auth.back_to_login') }}
            </NuxtLink>
        </div>
    </AuthCard>
</template>
