<script setup lang="ts">
    import type { CategoryNode } from '~/types/category';

    const props = defineProps<{
        modelValue: number | null;
        categories: CategoryNode[];
        placeholder?: string;
    }>();

    const emit = defineEmits<{
        'update:modelValue': [value: number | null];
    }>();

    function flatten(nodes: CategoryNode[], depth = 0): { label: string; value: number }[] {
        const out: { label: string; value: number }[] = [];
        const pad = '\u2003'.repeat(depth);
        for (const n of nodes) {
            out.push({ label: `${pad}${n.name_ar}`, value: n.id });
            if (n.children?.length) {
                out.push(...flatten(n.children, depth + 1));
            }
        }
        return out;
    }

    const options = computed(() => flatten(props.categories));

    const inner = computed({
        get: () => props.modelValue ?? undefined,
        set: (v: string | number | object | undefined) => {
            if (v === undefined || v === null || typeof v === 'object') {
                emit('update:modelValue', null);
                return;
            }
            emit('update:modelValue', typeof v === 'number' ? v : Number(v));
        },
    });
</script>

<template>
    <USelect
        v-model="inner"
        :items="options"
        :placeholder="placeholder ?? 'اختر التصنيف'"
        class="w-full max-w-md"
    />
</template>
