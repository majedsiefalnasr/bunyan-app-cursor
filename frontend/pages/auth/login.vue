<script setup lang="ts">
    import { z } from 'zod';
    import type { NuxtUiFormSubmitEvent } from '~/types/nuxt-ui-form';

    definePageMeta({
        layout: 'auth',
    });

    const { login } = useAuth();

    const schema = z.object({
        email: z.string().email('البريد الإلكتروني غير صحيح'),
        password: z.string().min(8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل'),
    });

    type LoginSchema = z.output<typeof schema>;

    const state = reactive<LoginSchema>({
        email: '',
        password: '',
    });

    const loading = ref(false);
    const error = ref<string | null>(null);

    async function onSubmit(event: NuxtUiFormSubmitEvent<LoginSchema>) {
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
    <div>
        <h2 class="mb-6 text-center text-xl font-semibold text-[#171717] dark:text-white">
            {{ $t('auth.login') }}
        </h2>

        <UAlert
            v-if="error"
            color="red"
            variant="subtle"
            :title="error"
            class="mb-4"
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
                <UInput
                    v-model="state.password"
                    type="password"
                    :placeholder="$t('auth.password_placeholder')"
                    icon="i-heroicons-lock-closed"
                    size="lg"
                />
            </UFormField>

            <div class="flex items-center justify-end">
                <NuxtLink
                    to="/ar/auth/forgot-password"
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
                    to="/ar/auth/register"
                    class="font-medium text-[#171717] hover:underline dark:text-white"
                >
                    {{ $t('auth.register') }}
                </NuxtLink>
            </p>
        </div>
    </div>
</template>
