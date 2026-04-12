<script setup lang="ts">
    import { computed } from 'vue';

    const { locale } = useI18n();

    const dir = computed(() => (locale.value === 'ar' ? 'rtl' : 'ltr'));
</script>

<template>
    <div
        :dir="dir"
        class="flex min-h-dvh flex-col bg-[#fafafa] font-sans dark:bg-[#0a0a0a]"
        style="box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.06)"
    >
        <div
            class="flex flex-1 flex-col items-center justify-center px-4 py-8 sm:px-6 md:px-8 lg:px-0 lg:py-12"
        >
            <div class="flex w-full max-w-md flex-col gap-8 md:max-w-xl lg:max-w-md">
                <div v-if="$slots.brand" class="text-center">
                    <slot name="brand" />
                </div>
                <div class="w-full">
                    <slot />
                </div>
                <div v-if="$slots.footer" class="text-center text-sm text-[#666666]">
                    <slot name="footer" />
                </div>
            </div>
        </div>
    </div>
</template>
