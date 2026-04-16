<script setup lang="ts">
    import type { FormSubmitEvent } from '@nuxt/ui';
    import { forgotPasswordSchema } from '~/schemas/auth';
    import type { ForgotPasswordFormValues } from '~/schemas/auth';

    definePageMeta({
        layout: 'auth',
    });

    const authApi = useAuthApi();
    const localePath = useLocalePath();

    const schema = forgotPasswordSchema;

    type ForgotSchema = ForgotPasswordFormValues;

    const loading = ref(false);
    const success = ref(false);
    const error = ref<string | null>(null);

    async function onSubmit(event: FormSubmitEvent<ForgotSchema>) {
        loading.value = true;
        error.value = null;

        try {
            const email = event.data.email;
            await authApi.forgotPassword(email);
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
            color="success"
            variant="subtle"
            :title="$t('auth.reset_link_sent')"
            class="mb-4"
        />

        <UAlert
            v-if="error"
            color="error"
            variant="subtle"
            role="alert"
            :title="error"
            class="mb-4"
            @close="error = null"
        />

        <UAuthForm
            v-if="!success"
            :schema="schema"
            :fields="[
                {
                    name: 'email',
                    type: 'email',
                    label: $t('auth.email'),
                    placeholder: $t('auth.email_placeholder'),
                    required: true,
                },
            ]"
            :submit="{ label: $t('auth.send_reset_link'), block: true, size: 'lg', loading }"
            :ui="{ root: 'space-y-4' }"
            @submit="onSubmit"
        >
            <template #validation>
                <UAlert
                    v-if="error"
                    color="error"
                    variant="subtle"
                    role="alert"
                    :title="error"
                    class="mb-2"
                    @close="error = null"
                />
            </template>
        </UAuthForm>

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
