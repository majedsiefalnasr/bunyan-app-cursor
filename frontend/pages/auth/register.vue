<script setup lang="ts">
    import type { AuthFormField, FormSubmitEvent } from '@nuxt/ui';
    import { registerSchema } from '~/schemas/auth';
    import type { RegisterFormValues } from '~/schemas/auth';
    import type { UserRole } from '~/types/auth';

    definePageMeta({
        layout: 'auth',
    });

    const { register } = useAuth();
    const authApi = useAuthApi();
    const localePath = useLocalePath();
    const { t } = useI18n();

    const schema = registerSchema;

    const loading = ref(false);
    const submitError = ref<string | null>(null);
    const success = ref(false);
    const submittedEmail = ref('');
    const accountType = ref<Extract<UserRole, 'customer' | 'contractor'> | null>(null);
    const accountTypeError = ref<string | null>(null);

    const resendLoading = ref(false);
    const resendSuccess = ref(false);
    const resendError = ref<string | null>(null);
    const cooldown = ref(0);

    let cooldownTimer: ReturnType<typeof setInterval> | null = null;

    const authForm = ref<{ state?: Record<string, unknown> } | null>(null);
    const formState = computed<Record<string, unknown>>(
        () => (authForm.value?.state ?? {}) as Record<string, unknown>
    );
    const passwordValue = computed<string>(() => String(formState.value.password ?? ''));
    const passwordConfirmationValue = computed<string>(() =>
        String(formState.value.password_confirmation ?? '')
    );

    const passwordChecks = computed<{
        minLength: boolean;
        hasLower: boolean;
        hasUpper: boolean;
        hasNumber: boolean;
        matches: boolean;
    }>(() => {
        const pwd = passwordValue.value;
        return {
            minLength: pwd.length >= 8,
            hasLower: /[a-z]/.test(pwd),
            hasUpper: /[A-Z]/.test(pwd),
            hasNumber: /[0-9]/.test(pwd),
            matches: pwd.length > 0 && pwd === passwordConfirmationValue.value,
        };
    });

    const passwordCheckItems = computed<
        Array<{ key: 'minLength' | 'hasUpperLower' | 'hasNumber'; label: string; ok: boolean }>
    >(() => [
        {
            key: 'minLength',
            label: t('auth.password_rules.min_length'),
            ok: passwordChecks.value.minLength,
        },
        {
            key: 'hasUpperLower',
            label: t('auth.password_rules.upper_lower'),
            ok: passwordChecks.value.hasLower && passwordChecks.value.hasUpper,
        },
        {
            key: 'hasNumber',
            label: t('auth.password_rules.number'),
            ok: passwordChecks.value.hasNumber,
        },
    ]);

    const canSubmit = computed(() => {
        const c = passwordChecks.value;
        return (
            !!accountType.value &&
            c.minLength &&
            c.hasLower &&
            c.hasUpper &&
            c.hasNumber &&
            c.matches
        );
    });

    const fields = computed<AuthFormField[]>(() => [
        {
            name: 'name',
            type: 'text',
            label: t('auth.name'),
            placeholder: t('auth.name_placeholder'),
            required: true,
        },
        {
            name: 'email',
            type: 'email',
            label: t('auth.email'),
            placeholder: t('auth.email_placeholder'),
            required: true,
        },
        {
            name: 'phone',
            type: 'tel',
            label: t('auth.phone'),
            placeholder: t('auth.phone_placeholder'),
            required: false,
        },
        {
            name: 'password',
            type: 'password',
            label: t('auth.password'),
            placeholder: t('auth.password_placeholder'),
            required: true,
        },
        {
            name: 'password_confirmation',
            type: 'password',
            label: t('auth.password_confirmation'),
            placeholder: t('auth.password_confirmation_placeholder'),
            required: true,
        },
    ]);

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
            // Prefer first validation detail if present (especially for VALIDATION_ERROR).
            const details = p.error?.details;
            if (details && typeof details === 'object') {
                const first = Object.values(details)[0];
                if (typeof first === 'string' && first) return first;
                if (Array.isArray(first) && typeof first[0] === 'string') return first[0];
            }
            const errors = p.errors;
            if (errors && typeof errors === 'object') {
                const first = Object.values(errors)[0];
                if (typeof first === 'string' && first) return first;
                if (Array.isArray(first) && typeof first[0] === 'string') return first[0];
            }

            if (typeof p.error?.message === 'string' && p.error.message) return p.error.message;
            if (typeof p.message === 'string' && p.message) return p.message;
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

    async function onSubmit(
        event: FormSubmitEvent<RegisterFormValues & { account_type?: string }>
    ) {
        loading.value = true;
        submitError.value = null;
        accountTypeError.value = null;
        resendSuccess.value = false;
        resendError.value = null;
        try {
            if (!accountType.value) {
                accountTypeError.value = t('auth.account_type');
                return;
            }
            await register(
                {
                    name: event.data.name,
                    email: event.data.email,
                    password: event.data.password,
                    password_confirmation: event.data.password_confirmation,
                    phone: event.data.phone || undefined,
                },
                { skipPostRegisterNavigation: true }
            );

            submittedEmail.value = event.data.email;
            success.value = true;
        } catch (e: unknown) {
            submitError.value = messageFromAuthCatch(e) || t('auth.unexpected_error');
        } finally {
            loading.value = false;
        }
    }

    async function resendVerification() {
        if (cooldown.value > 0) return;
        resendLoading.value = true;
        resendError.value = null;
        resendSuccess.value = false;
        try {
            await authApi.resendEmailVerification();
            resendSuccess.value = true;
            cooldown.value = 60;
            cooldownTimer = setInterval(() => {
                cooldown.value -= 1;
                if (cooldown.value <= 0 && cooldownTimer) {
                    clearInterval(cooldownTimer);
                    cooldownTimer = null;
                }
            }, 1000);
        } catch (e: unknown) {
            const err = e as { data?: { error?: { message?: string } } };
            resendError.value = err?.data?.error?.message || t('auth.unexpected_error');
        } finally {
            resendLoading.value = false;
        }
    }

    function goVerifyPage() {
        void navigateTo(localePath('/auth/verify-email'));
    }

    onUnmounted(() => {
        if (cooldownTimer) clearInterval(cooldownTimer);
    });
</script>

<template>
    <AuthCard :title="$t('auth.register')" :description="$t('auth.role_customer_description')">
        <div v-if="success" class="space-y-4 text-center" data-testid="register-step-done">
            <div class="flex justify-center">
                <UIcon name="i-heroicons-envelope" class="h-12 w-12 text-[#0072f5]" />
            </div>
            <p class="text-sm text-[#666666]">
                {{ $t('auth.register_check_email_hint') }}
                <span class="font-medium text-[#171717] dark:text-white">{{ submittedEmail }}</span>
            </p>

            <UAlert
                v-if="resendSuccess"
                color="success"
                variant="subtle"
                :title="$t('auth.verification_sent')"
                class="text-start"
            />
            <UAlert
                v-if="resendError"
                color="error"
                variant="subtle"
                role="alert"
                :title="resendError"
                class="text-start"
                @close="resendError = null"
            />

            <UButton
                block
                size="lg"
                :loading="resendLoading"
                :disabled="cooldown > 0"
                @click="resendVerification"
            >
                <template v-if="cooldown > 0">
                    {{ $t('auth.resend_in', { seconds: cooldown }) }}
                </template>
                <template v-else>
                    {{ $t('auth.resend_verification') }}
                </template>
            </UButton>

            <UButton block variant="outline" size="lg" @click="goVerifyPage">
                {{ $t('auth.register_go_verify_page') }}
            </UButton>

            <div class="pt-2">
                <NuxtLink
                    :to="localePath('/auth/login')"
                    class="text-sm font-medium text-[#171717] hover:underline dark:text-white"
                >
                    {{ $t('auth.back_to_login') }}
                </NuxtLink>
            </div>
        </div>

        <UAuthForm
            v-else
            ref="authForm"
            :schema="schema"
            :fields="fields"
            :submit="{
                label: $t('auth.register_submit'),
                block: true,
                size: 'lg',
                loading,
                disabled: !canSubmit,
            }"
            :ui="{ root: 'space-y-4' }"
            @submit="onSubmit"
        >
            <template #validation>
                <UAlert
                    v-if="accountTypeError"
                    color="error"
                    variant="subtle"
                    role="alert"
                    :title="$t('auth.validation_failed')"
                    :description="$t('auth.account_type')"
                    class="mb-2"
                    @close="accountTypeError = null"
                />
                <UAlert
                    v-if="submitError"
                    color="error"
                    variant="subtle"
                    role="alert"
                    :title="submitError"
                    class="mb-2"
                    @close="submitError = null"
                />
            </template>

            <template #header>
                <RoleSelector v-model="accountType" />
            </template>

            <template #password-help>
                <div class="mt-2 space-y-2">
                    <p class="text-xs text-[#666666]">
                        {{ $t('auth.password_rules.title') }}
                    </p>
                    <ul class="space-y-1">
                        <li
                            v-for="item in passwordCheckItems"
                            :key="item.key"
                            class="flex items-center gap-2 text-xs"
                            :class="item.ok ? 'text-[#16a34a]' : 'text-[#808080]'"
                        >
                            <UIcon
                                :name="
                                    item.ok ? 'i-heroicons-check-circle' : 'i-heroicons-x-circle'
                                "
                                class="h-4 w-4"
                            />
                            <span>{{ item.label }}</span>
                        </li>
                    </ul>
                </div>
            </template>

            <template #footer>
                <div class="text-center text-sm text-[#666666]">
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
            </template>
        </UAuthForm>
    </AuthCard>
</template>
