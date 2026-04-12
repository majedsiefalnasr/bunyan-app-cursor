<script setup lang="ts">
    import type { z } from 'zod';
    import type { NuxtUiFormSubmitEvent } from '~/types/nuxt-ui-form';
    import { profileUpdateSchema } from '~/schemas/auth';

    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const { user, updateProfile } = useAuth();
    const { t } = useI18n();

    const schema = profileUpdateSchema;
    type ProfileForm = z.infer<typeof profileUpdateSchema>;

    const state = reactive<ProfileForm>({
        name: '',
        phone: '',
    });

    watch(
        () => user.value,
        (u) => {
            if (!u) return;
            state.name = u.name ?? '';
            state.phone = u.phone ?? '';
        },
        { immediate: true }
    );

    const loading = ref(false);
    const error = ref<string | null>(null);
    const success = ref(false);

    async function onSubmit(event: NuxtUiFormSubmitEvent<ProfileForm>) {
        loading.value = true;
        error.value = null;
        success.value = false;
        try {
            await updateProfile({
                name: event.data.name,
                phone: event.data.phone || null,
            });
            success.value = true;
        } catch (e: unknown) {
            const err = e as { data?: { error?: { message?: string } } };
            error.value = err?.data?.error?.message || t('errors.codes.SERVER_ERROR.message');
        } finally {
            loading.value = false;
        }
    }
</script>

<template>
    <div class="mx-auto max-w-lg space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                {{ $t('profile.title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('profile.subtitle') }}
            </p>
        </div>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="mb-4 text-sm text-[#4d4d4d]">
                <span class="text-[#666666]">{{ $t('auth.email') }}:</span>
                <span class="ms-1 font-medium text-[#171717] dark:text-white">{{
                    user?.email
                }}</span>
            </div>

            <UAlert
                v-if="success"
                color="green"
                variant="subtle"
                :title="$t('profile.save_success')"
                class="mb-4"
                @close="success = false"
            />

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
                    <UInput v-model="state.name" icon="i-heroicons-user" size="lg" />
                </UFormField>

                <UFormField :label="$t('auth.phone')" name="phone">
                    <UInput v-model="state.phone" type="tel" icon="i-heroicons-phone" size="lg" />
                </UFormField>

                <UButton type="submit" block size="lg" :loading="loading">
                    {{ $t('profile.save') }}
                </UButton>
            </UForm>
        </UCard>
    </div>
</template>
