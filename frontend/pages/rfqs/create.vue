<script setup lang="ts">
    // Compatibility route: stage spec expects /rfqs/create, canonical route is /rfqs/new
    await navigateTo('/rfqs/new', { redirectCode: 301 });
</script>

<template>
    <div />
</template>
