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
            lang: computed(() => (direction.value === 'rtl' ? 'ar' : locale.value)),
        },
    });
</script>

<template>
    <AppToastProvider />
    <AppErrorBoundary>
        <NuxtLayout>
            <NuxtPage />
        </NuxtLayout>
    </AppErrorBoundary>
</template>
