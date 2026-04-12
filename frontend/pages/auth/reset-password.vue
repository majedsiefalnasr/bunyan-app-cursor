<script setup lang="ts">
    import { z } from 'zod';
    import type { NuxtUiFormSubmitEvent } from '~/types/nuxt-ui-form';

    definePageMeta({
        layout: 'auth',
    });

    const route = useRoute();
    const { apiFetch } = useApi();

    const schema = z
        .object({
            password: z.string().min(8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل'),
            password_confirmation: z.string().min(8, 'تأكيد كلمة المرور مطلوب'),
        })
        .refine((data) => data.password === data.password_confirmation, {
            message: 'كلمتا المرور غير متطابقتين',
            path: ['password_confirmation'],
        });

    type ResetSchema = z.output<typeof schema>;

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
                navigateTo('/ar/auth/login');
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
    <div>
        <h2 class="mb-6 text-center text-xl font-semibold text-[#171717] dark:text-white">
            {{ $t('auth.reset_password') }}
        </h2>

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
                to="/ar/auth/login"
                class="font-medium text-[#171717] hover:underline dark:text-white"
            >
                {{ $t('auth.back_to_login') }}
            </NuxtLink>
        </div>
    </div>
</template>
