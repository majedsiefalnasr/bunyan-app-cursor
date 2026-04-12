<script setup lang="ts">
    const localePath = useLocalePath();
</script>

<template>
    <AuthLayout>
        <template #brand>
            <NuxtLink
                :to="localePath('/')"
                class="text-2xl font-semibold tracking-[-0.02em] text-[#171717] dark:text-white"
            >
                {{ $t('app.name') }}
            </NuxtLink>
        </template>

        <UCard
            class="w-full max-w-md shadow-[0_0_0_1px_rgba(0,0,0,0.08)] dark:shadow-[0_0_0_1px_rgba(255,255,255,0.08)] md:max-w-xl lg:max-w-md"
            :ui="{ body: { padding: 'p-6 sm:p-8' } }"
        >
            <slot />
        </UCard>

        <template #footer>
            <p>{{ $t('app.name') }} &copy; {{ new Date().getFullYear() }}</p>
        </template>
    </AuthLayout>
</template>
