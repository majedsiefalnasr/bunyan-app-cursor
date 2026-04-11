<script setup lang="ts">
    import type { ErrorPayload } from '~/types/errors';

    defineProps<{
        payload: ErrorPayload;
        severity: 'error' | 'warning';
    }>();

    defineEmits<{
        close: [];
    }>();
</script>

<template>
    <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]" :ui="{ body: { padding: 'p-4' } }">
        <div class="flex items-start justify-between gap-3" dir="rtl">
            <div class="text-start">
                <p class="text-sm font-medium text-[#171717]">{{ payload.code }}</p>
                <p class="text-sm text-[#4d4d4d]">{{ payload.message }}</p>
            </div>
            <UButton color="white" variant="ghost" size="xs" @click="$emit('close')">×</UButton>
        </div>
        <div v-if="payload.details" class="mt-3 text-start text-xs text-[#808080]">
            {{ JSON.stringify(payload.details) }}
        </div>
    </UCard>
</template>
