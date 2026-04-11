<script setup lang="ts">
    import { reloadNuxtApp } from '#app';
    import { onErrorCaptured, ref } from 'vue';

    const errorState = ref<{
        error: Error | null;
        hasError: boolean;
        errorCode: string;
    }>({
        error: null,
        hasError: false,
        errorCode: '',
    });

    const isDev = import.meta.dev;

    function resetError() {
        errorState.value = {
            error: null,
            hasError: false,
            errorCode: '',
        };
    }

    onErrorCaptured((err) => {
        errorState.value = {
            error: err instanceof Error ? err : new Error(String(err)),
            hasError: true,
            errorCode: 'CLIENT_ERROR',
        };
        return false;
    });
</script>

<template>
    <div
        v-if="errorState.hasError"
        class="flex min-h-screen items-center justify-center bg-white px-4"
    >
        <div
            class="w-full max-w-md rounded-lg bg-white p-6 text-center shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
        >
            <h1 class="mb-2 text-2xl font-semibold tracking-tight text-[#171717]">حدث خطأ</h1>
            <p v-if="isDev" class="mb-4 text-sm text-[#4d4d4d]">
                {{ errorState.error?.message }}
            </p>
            <p v-else class="mb-4 text-sm text-[#4d4d4d]">يرجى المحاولة مرة أخرى.</p>
            <div class="flex flex-col gap-2">
                <UButton color="black" block @click="resetError">
                    {{ $t('errors.back') }}
                </UButton>
                <UButton color="white" variant="outline" block @click="reloadNuxtApp()">
                    {{ $t('errors.refresh') }}
                </UButton>
            </div>
            <p v-if="isDev" class="mt-4 text-xs text-[#808080]">
                {{ errorState.errorCode }}
            </p>
        </div>
    </div>
    <slot v-else />
</template>
