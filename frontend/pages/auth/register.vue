<script setup lang="ts">
    import type { ZodError } from 'zod';
    import type { UserRole } from '~/types/auth';
    import type { NuxtUiFormSubmitEvent } from '~/types/nuxt-ui-form';
    import {
        registerWizardAccountSchema,
        registerWizardCredentialsSchema,
        registerWizardPersonalSchema,
    } from '~/schemas/auth';

    type WizardState = {
        step: number;
        accountType: Extract<UserRole, 'customer' | 'contractor'> | null;
        name: string;
        email: string;
        phone: string;
        password: string;
        password_confirmation: string;
    };

    definePageMeta({
        layout: 'auth',
    });

    const { register } = useAuth();
    const authApi = useAuthApi();
    const localePath = useLocalePath();
    const { t } = useI18n();

    const wizard = useState<WizardState>('auth-register-wizard', () => ({
        step: 0,
        accountType: null,
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
    }));

    const stepError = ref<string | null>(null);
    const submitError = ref<string | null>(null);
    const loading = ref(false);
    const resendLoading = ref(false);
    const resendSuccess = ref(false);
    const resendError = ref<string | null>(null);
    const cooldown = ref(0);

    let cooldownTimer: ReturnType<typeof setInterval> | null = null;

    const totalSteps = 4;

    const bannerError = computed(() => stepError.value || submitError.value || '');

    const stepHeadline = computed(() => {
        switch (wizard.value.step) {
            case 0:
                return t('auth.register_step_account');
            case 1:
                return t('auth.register_step_personal');
            case 2:
                return t('auth.register_step_credentials');
            default:
                return t('auth.register_step_done');
        }
    });

    function firstZodMessage(err: ZodError) {
        const { fieldErrors, formErrors } = err.flatten();
        for (const messages of Object.values(fieldErrors)) {
            if (messages?.[0]) {
                return messages[0];
            }
        }
        if (formErrors.length) {
            return formErrors[0];
        }
        return t('auth.validation_failed');
    }

    function goBack() {
        stepError.value = null;
        submitError.value = null;
        if (wizard.value.step > 0) {
            wizard.value.step -= 1;
        }
    }

    function validateCurrentStep(): boolean {
        stepError.value = null;
        const w = wizard.value;
        if (w.step === 0) {
            const r = registerWizardAccountSchema.safeParse({ accountType: w.accountType });
            if (!r.success) {
                stepError.value = firstZodMessage(r.error);
                return false;
            }
        } else if (w.step === 1) {
            const r = registerWizardPersonalSchema.safeParse({ name: w.name, email: w.email });
            if (!r.success) {
                stepError.value = firstZodMessage(r.error);
                return false;
            }
        } else if (w.step === 2) {
            const r = registerWizardCredentialsSchema.safeParse({
                phone: credentialsState.phone,
                password: credentialsState.password,
                password_confirmation: credentialsState.password_confirmation,
            });
            if (!r.success) {
                stepError.value = firstZodMessage(r.error);
                return false;
            }
        }
        return true;
    }

    function goNext() {
        if (!validateCurrentStep()) return;
        if (wizard.value.step === 1) {
            credentialsState.phone = wizard.value.phone || '';
            credentialsState.password = '';
            credentialsState.password_confirmation = '';
        }
        wizard.value.step += 1;
    }

    async function submitRegistration() {
        wizard.value.phone = credentialsState.phone;
        wizard.value.password = credentialsState.password;
        wizard.value.password_confirmation = credentialsState.password_confirmation;
        if (!validateCurrentStep()) return;
        loading.value = true;
        submitError.value = null;
        try {
            const w = wizard.value;
            await register(
                {
                    name: w.name,
                    email: w.email,
                    password: w.password,
                    password_confirmation: w.password_confirmation,
                    phone: w.phone || undefined,
                },
                { skipPostRegisterNavigation: true }
            );
            wizard.value.step = 3;
        } catch (e: unknown) {
            const err = e as { data?: { error?: { message?: string } } };
            submitError.value = err?.data?.error?.message || t('auth.unexpected_error');
        } finally {
            loading.value = false;
        }
    }

    async function resendFromWizard() {
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
        if (cooldownTimer) {
            clearInterval(cooldownTimer);
        }
    });

    type CredentialsForm = {
        phone: string;
        password: string;
        password_confirmation: string;
    };

    const credentialsState = reactive<CredentialsForm>({
        phone: '',
        password: '',
        password_confirmation: '',
    });

    onMounted(() => {
        if (wizard.value.step === 2) {
            credentialsState.phone = wizard.value.phone || '';
            credentialsState.password = wizard.value.password || '';
            credentialsState.password_confirmation = wizard.value.password_confirmation || '';
        }
    });

    async function onCredentialsSubmit(_event: NuxtUiFormSubmitEvent<CredentialsForm>) {
        await submitRegistration();
    }
</script>

<template>
    <AuthCard :title="$t('auth.register')" :description="stepHeadline">
        <p class="mb-4 text-center text-sm text-[#666666]" data-testid="register-step-indicator">
            {{ $t('auth.register_step_of', { current: wizard.step + 1, total: totalSteps }) }}
        </p>

        <div class="mb-6 flex justify-center gap-2" role="list" aria-label="Registration progress">
            <span
                v-for="i in totalSteps"
                :key="i"
                class="h-2 w-8 rounded-full transition-colors"
                :class="i - 1 <= wizard.step ? 'bg-[#0072f5]' : 'bg-[#e5e5e5] dark:bg-neutral-700'"
                :data-active="i - 1 === wizard.step"
                :data-testid="'register-step-dot-' + (i - 1)"
            />
        </div>

        <UAlert
            v-if="bannerError"
            color="red"
            variant="subtle"
            role="alert"
            :title="bannerError"
            class="mb-4"
            @close="
                stepError = null;
                submitError = null;
            "
        />

        <!-- Step 1: account type -->
        <div v-if="wizard.step === 0" class="space-y-4">
            <RoleSelector v-model="wizard.accountType" />
            <div class="flex justify-end gap-2">
                <UButton color="primary" data-testid="register-next" @click="goNext">
                    {{ $t('auth.register_next') }}
                </UButton>
            </div>
        </div>

        <!-- Step 2: personal -->
        <div v-else-if="wizard.step === 1" class="space-y-4">
            <UFormField :label="$t('auth.name')" name="name">
                <UInput
                    v-model="wizard.name"
                    :placeholder="$t('auth.name_placeholder')"
                    icon="i-heroicons-user"
                    size="lg"
                />
            </UFormField>
            <UFormField :label="$t('auth.email')" name="email">
                <UInput
                    v-model="wizard.email"
                    type="email"
                    :placeholder="$t('auth.email_placeholder')"
                    icon="i-heroicons-envelope"
                    size="lg"
                />
            </UFormField>
            <div class="flex justify-between gap-2">
                <UButton color="gray" variant="ghost" @click="goBack">
                    {{ $t('auth.register_back') }}
                </UButton>
                <UButton color="primary" data-testid="register-next" @click="goNext">
                    {{ $t('auth.register_next') }}
                </UButton>
            </div>
        </div>

        <!-- Step 3: contact + password -->
        <div v-else-if="wizard.step === 2">
            <UForm
                :schema="registerWizardCredentialsSchema"
                :state="credentialsState"
                class="space-y-4"
                @submit="onCredentialsSubmit"
            >
                <UFormField :label="$t('auth.phone')" name="phone">
                    <UInput
                        v-model="credentialsState.phone"
                        type="tel"
                        :placeholder="$t('auth.phone_placeholder')"
                        icon="i-heroicons-phone"
                        size="lg"
                    />
                </UFormField>

                <UFormField :label="$t('auth.password')" name="password">
                    <UInput
                        v-model="credentialsState.password"
                        type="password"
                        :placeholder="$t('auth.password_placeholder')"
                        icon="i-heroicons-lock-closed"
                        size="lg"
                    />
                </UFormField>

                <PasswordStrength :password="credentialsState.password || ''" />

                <UFormField :label="$t('auth.password_confirmation')" name="password_confirmation">
                    <UInput
                        v-model="credentialsState.password_confirmation"
                        type="password"
                        :placeholder="$t('auth.password_confirmation_placeholder')"
                        icon="i-heroicons-lock-closed"
                        size="lg"
                    />
                </UFormField>

                <div class="flex justify-between gap-2 pt-2">
                    <UButton type="button" color="gray" variant="ghost" @click="goBack">
                        {{ $t('auth.register_back') }}
                    </UButton>
                    <UButton
                        type="submit"
                        color="primary"
                        :loading="loading"
                        data-testid="register-submit"
                    >
                        {{ $t('auth.register_submit') }}
                    </UButton>
                </div>
            </UForm>
        </div>

        <!-- Step 4: pending verification -->
        <div v-else class="space-y-4 text-center" data-testid="register-step-done">
            <div class="flex justify-center">
                <UIcon name="i-heroicons-envelope" class="h-12 w-12 text-[#0072f5]" />
            </div>
            <p class="text-sm text-[#666666]">
                {{ $t('auth.register_check_email_hint') }}
                <span class="font-medium text-[#171717] dark:text-white">{{ wizard.email }}</span>
            </p>

            <UAlert
                v-if="resendSuccess"
                color="green"
                variant="subtle"
                :title="$t('auth.verification_sent')"
                class="text-start"
            />
            <UAlert
                v-if="resendError"
                color="red"
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
                @click="resendFromWizard"
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

        <div v-if="wizard.step < 3" class="mt-6 text-center text-sm text-[#666666]">
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
