<script setup lang="ts">
    import { z } from 'zod';
    import type { NuxtUiFormSubmitEvent } from '~/types/nuxt-ui-form';

    definePageMeta({
        layout: 'auth',
    });

    const { register } = useAuth();

    const schema = z
        .object({
            name: z.string().min(1, 'الاسم مطلوب').max(255),
            email: z.string().email('البريد الإلكتروني غير صحيح'),
            password: z.string().min(8, 'كلمة المرور يجب أن تكون 8 أحرف على الأقل'),
            password_confirmation: z.string().min(8, 'تأكيد كلمة المرور مطلوب'),
            phone: z.string().max(20).optional().or(z.literal('')),
        })
        .refine((data) => data.password === data.password_confirmation, {
            message: 'كلمتا المرور غير متطابقتين',
            path: ['password_confirmation'],
        });

    type RegisterSchema = z.output<typeof schema>;

    const state = reactive<Partial<RegisterSchema>>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        phone: '',
    });

    const loading = ref(false);
    const error = ref<string | null>(null);

    async function onSubmit(event: NuxtUiFormSubmitEvent<RegisterSchema>) {
        loading.value = true;
        error.value = null;

        try {
            await register({
                name: event.data.name,
                email: event.data.email,
                password: event.data.password,
                password_confirmation: event.data.password_confirmation,
                phone: event.data.phone || undefined,
            });
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
            {{ $t('auth.register') }}
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
            <UFormField :label="$t('auth.name')" name="name">
                <UInput
                    v-model="state.name"
                    :placeholder="$t('auth.name_placeholder')"
                    icon="i-heroicons-user"
                    size="lg"
                />
            </UFormField>

            <UFormField :label="$t('auth.email')" name="email">
                <UInput
                    v-model="state.email"
                    type="email"
                    :placeholder="$t('auth.email_placeholder')"
                    icon="i-heroicons-envelope"
                    size="lg"
                />
            </UFormField>

            <UFormField :label="$t('auth.phone')" name="phone">
                <UInput
                    v-model="state.phone"
                    type="tel"
                    :placeholder="$t('auth.phone_placeholder')"
                    icon="i-heroicons-phone"
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
                {{ $t('auth.register') }}
            </UButton>
        </UForm>

        <div class="mt-6 text-center text-sm text-[#666666]">
            <p>
                {{ $t('auth.has_account') }}
                <NuxtLink
                    to="/ar/auth/login"
                    class="font-medium text-[#171717] hover:underline dark:text-white"
                >
                    {{ $t('auth.login') }}
                </NuxtLink>
            </p>
        </div>
    </div>
</template>
