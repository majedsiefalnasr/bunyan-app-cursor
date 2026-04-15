<script setup lang="ts">
    const { initDirection, direction } = useDirection();
    const { locale } = useI18n();
    const runtimeConfig = useRuntimeConfig();

    onMounted(() => {
        initDirection();
        if (runtimeConfig.public.playwrightTest === true) {
            document.documentElement.setAttribute('data-pw-hydrated', '1');
        }
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
