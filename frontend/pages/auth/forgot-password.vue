<script setup lang="ts">
    import type { NuxtUiFormSubmitEvent } from '~/types/nuxt-ui-form';
    import { forgotPasswordSchema } from '~/schemas/auth';
    import type { ForgotPasswordFormValues } from '~/schemas/auth';

    definePageMeta({
        layout: 'auth',
    });

    const authApi = useAuthApi();
    const localePath = useLocalePath();

    const schema = forgotPasswordSchema;

    type ForgotSchema = ForgotPasswordFormValues;

    const state = reactive<ForgotSchema>({
        email: '',
    });

    const loading = ref(false);
    const success = ref(false);
    const error = ref<string | null>(null);

    async function onSubmit(event: NuxtUiFormSubmitEvent<ForgotSchema>) {
        loading.value = true;
        error.value = null;

        try {
            await authApi.forgotPassword(event.data.email);
            success.value = true;
        } catch (e: unknown) {
            const err = e as { data?: { error?: { message?: string } } };
            error.value = err?.data?.error?.message || 'حدث خطأ غير متوقع';
        } finally {
            loading.value = false;
        }
    }
</script>

<template>
    <AuthCard
        :title="$t('auth.forgot_password')"
        :description="$t('auth.forgot_password_description')"
    >
        <UAlert
            v-if="success"
            color="green"
            variant="subtle"
            :title="$t('auth.reset_link_sent')"
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
            <UFormField :label="$t('auth.email')" name="email">
                <UInput
                    v-model="state.email"
                    type="email"
                    :placeholder="$t('auth.email_placeholder')"
                    icon="i-heroicons-envelope"
                    size="lg"
                />
            </UFormField>

            <UButton type="submit" block size="lg" :loading="loading">
                {{ $t('auth.send_reset_link') }}
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
