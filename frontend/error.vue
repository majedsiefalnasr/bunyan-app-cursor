<script setup lang="ts">
    import type { NuxtError } from '#app';

    const props = defineProps<{
        error: NuxtError;
    }>();

    const { t } = useI18n();

    const statusCode = computed(() => props.error.statusCode ?? 500);

    const titleKey = computed(() => {
        if (statusCode.value === 404) return 'errors.pages.404.title';
        if (statusCode.value === 403) return 'errors.pages.403.title';
        return 'errors.pages.500.title';
    });

    const descriptionKey = computed(() => {
        if (statusCode.value === 404) return 'errors.pages.404.description';
        if (statusCode.value === 403) return 'errors.pages.403.description';
        return 'errors.pages.500.description';
    });

    function handleRetry() {
        clearError({ redirect: '/' });
    }

    useHead({ title: () => t(titleKey.value) });
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-[#fafafa] p-6 dark:bg-[#0a0a0a]"
    >
        <div class="w-full max-w-lg">
            <UAlert
                role="alert"
                color="error"
                variant="subtle"
                :title="$t(titleKey)"
                :description="$t(descriptionKey)"
                icon="i-heroicons-exclamation-triangle"
                class="mb-6"
            />

            <div class="flex flex-wrap gap-3">
                <UButton color="primary" icon="i-heroicons-arrow-path" @click="handleRetry">
                    {{ $t('errors.retry') }}
                </UButton>
                <UButton color="neutral" variant="outline" icon="i-heroicons-home" to="/">
                    {{ $t('errors.goHome') }}
                </UButton>
            </div>
        </div>
    </div>
</template>
