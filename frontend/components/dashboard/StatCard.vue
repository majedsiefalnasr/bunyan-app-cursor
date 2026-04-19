<script setup lang="ts">
    interface Props {
        icon: string;
        label: string;
        value: string | number;
        trend?: number;
        trendLabel?: string;
        bgColor?: string;
        textColor?: string;
    }

    withDefaults(defineProps<Props>(), {
        trend: 0,
        trendLabel: 'مقارنة بالشهر الماضي',
        bgColor: 'bg-blue-50',
        textColor: 'text-blue-600',
    });
</script>

<template>
    <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]">
        <div class="flex items-center justify-between">
            <div class="space-y-2">
                <p class="text-sm text-[#666666] font-medium uppercase">
                    {{ label }}
                </p>
                <p class="text-3xl font-bold text-[#171717] tracking-tight">
                    {{ value }}
                </p>
                <p
                    v-if="trend !== 0"
                    class="text-xs"
                    :class="trend > 0 ? 'text-green-600' : 'text-red-600'"
                >
                    <span>{{ trend > 0 ? '↑' : '↓' }} {{ Math.abs(trend) }}%</span>
                    <span class="text-[#666666]"> {{ trendLabel }}</span>
                </p>
            </div>
            <div :class="[bgColor, 'rounded-lg p-4']">
                <UIcon :name="icon" :class="[textColor, 'text-3xl']" />
            </div>
        </div>
    </UCard>
</template>
