<script setup lang="ts">
    const { initDirection, direction } = useDirection();
    const { locale } = useI18n();

    onMounted(() => {
        initDirection();
        // Used by Playwright E2E to detect client hydration reliably.
        document.documentElement.setAttribute('data-pw-hydrated', '1');
    });

    useHead({
        htmlAttrs: {
            dir: direction,
            lang: computed(() => locale.value),
        },
    });
</script>

<template>
    <UApp
        :dir="direction"
        :locale="{ code: locale, name: locale, dir: direction, messages: {} as any }"
    >
        <AppErrorBoundary>
            <NuxtLayout>
                <NuxtPage />
            </NuxtLayout>
        </AppErrorBoundary>
    </UApp>
</template>
