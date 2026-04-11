<script setup lang="ts">
    import { reloadNuxtApp } from '#app';

    definePageMeta({
        layout: 'error',
    });
</script>

<template>
    <div class="text-center">
        <p class="mb-4 text-8xl font-semibold tracking-tighter text-[#ebebeb]">500</p>
        <h1 class="mb-2 text-3xl font-semibold tracking-tight text-[#171717]">
            {{ $t('errors.pages.500.title') }}
        </h1>
        <p class="mb-8 text-[#4d4d4d]">
            {{ $t('errors.pages.500.description') }}
        </p>
        <div class="flex flex-wrap justify-center gap-3">
            <UButton color="black" to="/">
                {{ $t('errors.goHome') }}
            </UButton>
            <UButton color="white" variant="outline" @click="reloadNuxtApp()">
                {{ $t('errors.refresh') }}
            </UButton>
        </div>
    </div>
</template>
