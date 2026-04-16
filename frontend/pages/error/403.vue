<script setup lang="ts">
    definePageMeta({
        layout: 'error',
    });
</script>

<template>
    <div class="text-center">
        <p class="mb-4 text-8xl font-semibold tracking-tighter text-[#ebebeb]">403</p>
        <h1 class="mb-2 text-3xl font-semibold tracking-tight text-[#171717]">
            {{ $t('errors.pages.403.title') }}
        </h1>
        <p class="mb-8 text-[#4d4d4d]">
            {{ $t('errors.pages.403.description') }}
        </p>
        <div class="flex flex-wrap justify-center gap-3">
            <UButton color="primary" to="/">
                {{ $t('errors.goHome') }}
            </UButton>
            <UButton color="neutral" variant="outline" to="/auth/login">
                {{ $t('auth.login') }}
            </UButton>
        </div>
    </div>
</template>
