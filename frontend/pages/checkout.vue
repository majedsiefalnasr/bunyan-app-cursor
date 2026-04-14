<script setup lang="ts">
    // Compatibility route: stage spec expects /checkout, canonical route is /payments/checkout
    await navigateTo('/payments/checkout', { redirectCode: 301 });
</script>

<template>
    <div />
</template>
