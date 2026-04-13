<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const localePath = useLocalePath();
    const { role } = useAuth();
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                {{ $t('nav.dashboard') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('dashboard.welcome_hint') }}
            </p>
        </div>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-[#4d4d4d]">
                    {{ $t('dashboard.profile_cta') }}
                </p>
                <UButton :to="localePath('/profile')" color="primary" variant="solid">
                    {{ $t('shell.user.profile') }}
                </UButton>
            </div>
        </UCard>

        <UCard v-if="role === 'customer'" class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-[#4d4d4d]">
                    {{ $t('dashboard.rfq_customer_cta') }}
                </p>
                <UButton :to="localePath('/rfqs')" color="primary" variant="solid">
                    {{ $t('nav.rfqs') }}
                </UButton>
            </div>
        </UCard>

        <UCard v-if="role === 'contractor'" class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-[#4d4d4d]">
                    {{ $t('dashboard.rfq_contractor_cta') }}
                </p>
                <UButton :to="localePath('/contractor/rfqs')" color="primary" variant="solid">
                    {{ $t('nav.rfq_invitations') }}
                </UButton>
            </div>
        </UCard>
    </div>
</template>
