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
    <UTable :rows="props.rows" :columns="columns" :loading="props.loading">
        <template #label-data="{ row }">
            <div class="text-sm text-[#171717]">
                {{ row.label || row.key }}
            </div>
            <div class="text-xs text-[#808080] font-mono" style="direction: ltr">
                {{ row.key }}
            </div>
        </template>
        <template #enabled-data="{ row }">
            <UCheckbox :model-value="row.enabled" disabled />
        </template>
    </UTable>
    <p v-if="!props.loading && props.rows.length === 0" class="mt-2 text-sm text-[#808080]">
        لا توجد صلاحيات لعرضها.
    </p>
</template>
