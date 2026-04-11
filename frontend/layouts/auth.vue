<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-[#fafafa] px-4 dark:bg-[#0a0a0a]"
    >
        <div class="mb-8 text-center">
            <NuxtLink
                to="/"
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
            >
                {{ $t('app.name') }}
            </NuxtLink>
        </div>
        <UCard class="w-full max-w-md">
            <slot />
        </UCard>
    </div>
</template>
