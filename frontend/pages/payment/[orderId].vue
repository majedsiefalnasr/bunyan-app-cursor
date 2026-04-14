<script setup lang="ts">
    // Compatibility route: stage spec expects /payment/:orderId, canonical route is /payments/:id
    const route = useRoute();
    const orderId = Array.isArray(route.params.orderId)
        ? route.params.orderId[0]
        : route.params.orderId;

    await navigateTo(`/payments/${encodeURIComponent(String(orderId))}`, { redirectCode: 302 });
</script>

<template>
    <div />
</template>
