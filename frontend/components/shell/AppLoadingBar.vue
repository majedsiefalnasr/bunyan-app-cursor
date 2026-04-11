<script setup lang="ts">
    const isLoading = ref(false);
    const nuxtApp = useNuxtApp();

    nuxtApp.hook('page:start', () => {
        isLoading.value = true;
    });

    nuxtApp.hook('page:finish', () => {
        isLoading.value = false;
    });
</script>

<template>
    <Transition name="fade">
        <UProgress
            v-if="isLoading"
            :aria-label="$t('shell.loading')"
            class="fixed start-0 top-0 z-50 w-full"
            size="xs"
            animation="carousel"
        />
    </Transition>
</template>

<style scoped>
    .fade-enter-active,
    .fade-leave-active {
        transition: opacity 0.2s;
    }
    .fade-enter-from,
    .fade-leave-to {
        opacity: 0;
    }
</style>
