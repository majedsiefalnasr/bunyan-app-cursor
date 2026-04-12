<script setup lang="ts">
    interface Props {
        title?: string;
        description?: string;
    }

    defineProps<Props>();
</script>

<template>
    <div
        class="auth-card font-sans text-[#171717] dark:text-white [&_button[type='submit']]:min-h-11 [&_input]:min-h-11"
    >
        <h2
            v-if="title"
            class="mb-2 text-center text-xl font-semibold tracking-[-0.02em] text-[#171717] dark:text-white"
        >
            {{ title }}
        </h2>
        <p
            v-if="description"
            class="mb-6 text-center text-sm font-normal tracking-tight text-[#666666]"
        >
            {{ description }}
        </p>
        <slot />
    </div>
</template>
