<script setup lang="ts">
    const { initDirection, direction } = useDirection();
    const { locale } = useI18n();

    onMounted(() => {
        initDirection();
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
