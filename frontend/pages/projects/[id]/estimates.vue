<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const route = useRoute();

    interface BoqLine {
        id: string;
        label: string;
        quantity: number;
        unit_price: number;
    }

    const projectId = computed(() => String(route.params.id));
    const storageKey = computed(() => `bunyan:boq:${projectId.value}`);

    const lines = ref<BoqLine[]>([]);

    function loadFromStorage() {
        if (!import.meta.client) {
            return;
        }
        try {
            const raw = sessionStorage.getItem(storageKey.value);
            if (!raw) {
                lines.value = [];
                return;
            }
            const parsed = JSON.parse(raw) as BoqLine[];
            lines.value = Array.isArray(parsed) ? parsed : [];
        } catch {
            lines.value = [];
        }
    }

    function persist() {
        if (!import.meta.client) {
            return;
        }
        sessionStorage.setItem(storageKey.value, JSON.stringify(lines.value));
    }

    const total = computed(() =>
        lines.value.reduce((sum, row) => sum + row.quantity * row.unit_price, 0)
    );

    function newLineId(): string {
        if (typeof crypto !== 'undefined' && 'randomUUID' in crypto) {
            return crypto.randomUUID();
        }
        return `row_${Date.now()}_${Math.random().toString(16).slice(2)}`;
    }

    function addRow() {
        lines.value.push({
            id: newLineId(),
            label: '',
            quantity: 1,
            unit_price: 0,
        });
    }

    function removeRow(id: string) {
        lines.value = lines.value.filter((r) => r.id !== id);
    }

    onMounted(loadFromStorage);
    watch(projectId, () => {
        loadFromStorage();
    });
    watch(lines, persist, { deep: true });
</script>

<template>
    <div class="space-y-4">
        <UAlert color="blue" variant="soft" :title="$t('projects.estimates_disclaimer')" />

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-medium text-[#171717] dark:text-white">{{
                        $t('projects.estimates_title')
                    }}</span>
                    <UButton size="sm" variant="soft" color="gray" @click="addRow">
                        {{ $t('projects.estimates_add_line') }}
                    </UButton>
                </div>
            </template>

            <div v-if="lines.length === 0" class="text-sm text-[#666666]">
                {{ $t('projects.estimates_empty') }}
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="row in lines"
                    :key="row.id"
                    class="grid gap-3 sm:grid-cols-12 sm:items-end"
                >
                    <UFormGroup class="sm:col-span-5" :label="$t('projects.estimates_col_label')">
                        <UInput v-model="row.label" />
                    </UFormGroup>
                    <UFormGroup class="sm:col-span-2" :label="$t('projects.estimates_col_qty')">
                        <UInput v-model.number="row.quantity" type="number" min="0" step="0.01" />
                    </UFormGroup>
                    <UFormGroup class="sm:col-span-3" :label="$t('projects.estimates_col_price')">
                        <UInput v-model.number="row.unit_price" type="number" min="0" step="0.01" />
                    </UFormGroup>
                    <div class="flex sm:col-span-2">
                        <UButton color="red" variant="soft" @click="removeRow(row.id)">
                            {{ $t('projects.estimates_remove') }}
                        </UButton>
                    </div>
                </div>
                <p class="text-sm font-medium text-[#171717] dark:text-white">
                    {{ $t('projects.estimates_total') }}:
                    {{ total.toFixed(2) }}
                </p>
            </div>
        </UCard>
    </div>
</template>
