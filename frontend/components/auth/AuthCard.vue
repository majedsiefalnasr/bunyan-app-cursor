<script setup lang="ts">
    interface Props {
        title?: string;
        description?: string;
    }

    defineProps<Props>();
</script>

<template>
    <div class="auth-card">
        <h2
            v-if="title"
            class="mb-2 text-center text-xl font-semibold tracking-tight text-[#171717] dark:text-white"
        >
            {{ title }}
        </h2>
        <p v-if="description" class="mb-6 text-center text-sm text-[#666666]">
            {{ description }}
        </p>
        <slot />
    </div>
</template>
