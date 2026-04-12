<script setup lang="ts">
    import type { NuxtUiFormSubmitEvent } from '~/types/nuxt-ui-form';
    import { registerSchema } from '~/schemas/auth';
    import type { RegisterFormValues } from '~/schemas/auth';

    definePageMeta({
        layout: 'auth',
    });

    const { register } = useAuth();
    const localePath = useLocalePath();

    const schema = registerSchema;

    const state = reactive<Partial<RegisterFormValues>>({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        phone: '',
    });

    const loading = ref(false);
    const error = ref<string | null>(null);

    async function onSubmit(event: NuxtUiFormSubmitEvent<RegisterFormValues>) {
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
    <AuthCard :title="$t('auth.register')">
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
                {{ $t('auth.register') }}
            </UButton>
        </UForm>

        <div class="mt-6 text-center text-sm text-[#666666]">
            <p>
                {{ $t('auth.has_account') }}
                <NuxtLink
                    :to="localePath('/auth/login')"
                    class="font-medium text-[#171717] hover:underline dark:text-white"
                >
                    {{ $t('auth.login') }}
                </NuxtLink>
            </p>
        </div>
    </AuthCard>
</template>
