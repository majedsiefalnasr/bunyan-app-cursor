<script setup lang="ts">
    import type { FormSubmitEvent } from '@nuxt/ui';
    import { resetPasswordSchema } from '~/schemas/auth';
    import type { ResetPasswordFormValues } from '~/schemas/auth';

    definePageMeta({
        layout: 'auth',
    });

    const route = useRoute();
    const authApi = useAuthApi();
    const localePath = useLocalePath();

    const schema = resetPasswordSchema;

    type ResetSchema = ResetPasswordFormValues;

    const loading = ref(false);
    const success = ref(false);
    const error = ref<string | null>(null);

    const token = computed(() => (route.query.token as string) || '');
    const email = computed(() => (route.query.email as string) || '');

    async function onSubmit(event: FormSubmitEvent<ResetSchema>) {
        loading.value = true;
        error.value = null;

        try {
            const pwd = event.data.password;
            const pwd2 = event.data.password_confirmation;
            await authApi.resetPassword({
                token: token.value,
                email: email.value,
                password: pwd,
                password_confirmation: pwd2,
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
            color="success"
            variant="subtle"
            :title="$t('auth.password_reset_success')"
            :description="$t('auth.redirecting_to_login')"
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
                    name: 'password',
                    type: 'password',
                    label: $t('auth.new_password'),
                    placeholder: $t('auth.password_placeholder'),
                    required: true,
                },
                {
                    name: 'password_confirmation',
                    type: 'password',
                    label: $t('auth.password_confirmation'),
                    placeholder: $t('auth.password_confirmation_placeholder'),
                    required: true,
                },
            ]"
            :submit="{ label: $t('auth.reset_password'), block: true, size: 'lg', loading }"
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
