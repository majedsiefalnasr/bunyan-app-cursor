<script setup lang="ts">
    const props = defineProps<{
        rows: { key: string; label?: string | null; enabled: boolean }[];
        loading?: boolean;
    }>();

    const columns = [
        { key: 'label', label: 'الصلاحية' },
        { key: 'enabled', label: 'مفعل' },
    ];
</script>

<template>
    <UTable :rows="props.rows as any" :columns="columns as any" :loading="props.loading">
        <template #label-data="{ row }">
            <div class="text-sm text-[#171717]">
                {{ (row as any).label || (row as any).key }}
            </div>
            <div class="text-xs text-[#808080] font-mono" style="direction: ltr">
                {{ (row as any).key }}
            </div>
        </template>
        <template #enabled-data="{ row }">
            <UCheckbox :model-value="(row as any).enabled" disabled />
        </template>
    </UTable>
    <p v-if="!props.loading && props.rows.length === 0" class="mt-2 text-sm text-[#808080]">
        لا توجد صلاحيات لعرضها.
    </p>
</template>
