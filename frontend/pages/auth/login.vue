<script setup lang="ts">
    import type { FormSubmitEvent } from '@nuxt/ui';
    import { loginSchema } from '~/schemas/auth';
    import type { LoginFormValues } from '~/schemas/auth';

    definePageMeta({
        layout: 'auth',
    });

    const authStore = useAuthStore();
    const route = useRoute();
    const localePath = useLocalePath();
    const { t, te } = useI18n();

    const schema = loginSchema;

    const loading = ref(false);
    const error = ref<string | null>(null);

    /** Normalize `$fetch` / ofetch error shapes (body on `data` or `response._data`). */
    function messageFromAuthCatch(err: unknown): string | null {
        if (!err || typeof err !== 'object') return null;
        const o = err as Record<string, unknown>;
        const fromData = (payload: unknown): string | null => {
            if (!payload || typeof payload !== 'object') return null;
            const p = payload as {
                message?: string | null;
                errors?: Record<string, unknown> | null;
                error?: {
                    code?: string;
                    message?: string;
                    details?: Record<string, unknown> | null;
                } | null;
            };
            const code = p.error?.code;
            if (code) {
                const key = `errors.codes.${code}.message`;
                if (te(key)) return t(key);
            }
            if (typeof p.error?.message === 'string' && p.error.message) {
                return p.error.message;
            }
            if (typeof p.message === 'string' && p.message) {
                return p.message;
            }
            // Validation: show first detail if present.
            const details = p.error?.details;
            if (details && typeof details === 'object') {
                const first = Object.values(details)[0];
                if (Array.isArray(first) && typeof first[0] === 'string') {
                    return first[0];
                }
            }
            const errors = p.errors;
            if (errors && typeof errors === 'object') {
                const first = Object.values(errors)[0];
                if (Array.isArray(first) && typeof first[0] === 'string') {
                    return first[0];
                }
            }
            return null;
        };
        const direct = fromData(o.data);
        if (direct) return direct;
        const res = o.response;
        if (res && typeof res === 'object' && '_data' in res) {
            return fromData((res as { _data?: unknown })._data);
        }
        if (typeof o.message === 'string' && o.message) return o.message;
        return null;
    }

    async function onSubmit(event: FormSubmitEvent<LoginFormValues & { remember?: boolean }>) {
        loading.value = true;
        error.value = null;

        try {
            const { email, password } = event.data;
            await authStore.login({ email, password });
            const redirectRaw = route.query.redirect;
            const redirect =
                typeof redirectRaw === 'string' && redirectRaw.startsWith('/') ? redirectRaw : null;
            await navigateTo(redirect ? redirect : localePath('/dashboard'));
        } catch (e: unknown) {
            error.value = messageFromAuthCatch(e) || 'حدث خطأ غير متوقع';
        } finally {
            loading.value = false;
        }
    }
</script>

<template>
    <AuthCard :title="$t('auth.login')">
        <UAuthForm
            :schema="schema"
            :fields="[
                {
                    name: 'email',
                    type: 'email',
                    label: $t('auth.email'),
                    placeholder: $t('auth.email_placeholder'),
                    required: true,
                },
                {
                    name: 'password',
                    type: 'password',
                    label: $t('auth.password'),
                    placeholder: $t('auth.password_placeholder'),
                    required: true,
                },
                {
                    name: 'remember',
                    type: 'checkbox',
                    label: $t('auth.remember_me'),
                },
            ]"
            :submit="{ label: $t('auth.login'), block: true, size: 'lg', loading }"
            :ui="{ root: 'space-y-4' }"
            @submit="onSubmit"
        >
            <template #validation>
                <div v-if="error" role="alert" data-testid="auth-error-alert" class="mb-2">
                    <UAlert color="error" variant="subtle" :title="error" @close="error = null" />
                </div>
            </template>

            <template #password-hint>
                <NuxtLink
                    :to="localePath('/auth/forgot-password')"
                    class="text-sm text-[#0072f5] hover:underline"
                    tabindex="-1"
                >
                    {{ $t('auth.forgot_password') }}
                </NuxtLink>
            </template>

            <template #footer>
                <div class="text-center text-sm text-[#666666]">
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
            </template>
        </UAuthForm>
    </AuthCard>
</template>
