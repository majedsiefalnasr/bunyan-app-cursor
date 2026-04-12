<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const localePath = useLocalePath();

    await navigateTo(localePath('/projects/create'), { replace: true });
</script>

<template>
    <div />
</template>
