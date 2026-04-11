<script setup lang="ts">
    const { items } = useBreadcrumb();
    const breadcrumbLinks = computed(() => [...items.value]);
</script>

<template>
    <nav
        v-if="breadcrumbLinks.length > 0"
        :aria-label="$t('shell.nav.breadcrumb')"
    >
        <UBreadcrumb :links="breadcrumbLinks" />
    </nav>
</template>
