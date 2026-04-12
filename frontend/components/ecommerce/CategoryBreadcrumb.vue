<script setup lang="ts">
    import type { CategoryNode } from '~/types/category';

    const props = defineProps<{
        /** Root-to-current trail (excluding current if passed separately) */
        trail: Pick<CategoryNode, 'id' | 'name_ar'>[];
        current?: Pick<CategoryNode, 'name_ar'> | null;
    }>();

    const items = computed(() => {
        const parts = props.trail.map((t) => t.name_ar);
        if (props.current?.name_ar) {
            parts.push(props.current.name_ar);
        }
        return parts;
    });
</script>

<template>
    <nav aria-label="مسار التصنيف" class="text-sm text-[#4d4d4d]">
        <ol class="flex flex-wrap items-center gap-1">
            <li v-for="(label, i) in items" :key="i" class="flex items-center gap-1">
                <span v-if="i > 0" class="text-[#808080]" aria-hidden="true">/</span>
                <span :class="i === items.length - 1 ? 'font-medium text-[#171717]' : ''">{{
                    label
                }}</span>
            </li>
        </ol>
    </nav>
</template>
