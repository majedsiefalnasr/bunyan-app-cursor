<script setup lang="ts">
    import { z } from 'zod';
    import type { FormSubmitEvent } from '@nuxt/ui';

    definePageMeta({
        layout: 'auth',
    });

    const { apiFetch } = useApi();

    const schema = z.object({
        email: z.string().email('البريد الإلكتروني غير صحيح'),
    });

    type ForgotSchema = z.output<typeof schema>;

    const state = reactive<ForgotSchema>({
        email: '',
    });

    const loading = ref(false);
    const success = ref(false);
    const error = ref<string | null>(null);

    async function onSubmit(event: FormSubmitEvent<ForgotSchema>) {
        loading.value = true;
        error.value = null;

        try {
            await apiFetch('/v1/auth/forgot-password', {
                method: 'POST',
                body: { email: event.data.email },
            });
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
    <div>
        <h2 class="mb-2 text-center text-xl font-semibold text-[#171717] dark:text-white">
            {{ $t('auth.forgot_password') }}
        </h2>
        <p class="mb-6 text-center text-sm text-[#666666]">
            {{ $t('auth.forgot_password_description') }}
        </p>

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
            :title="error"
            class="mb-4"
            :close-button="{ onClick: () => (error = null) }"
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
                to="/ar/auth/login"
                class="font-medium text-[#171717] hover:underline dark:text-white"
            >
                {{ $t('auth.back_to_login') }}
            </NuxtLink>
        </div>
    </div>
</template>
