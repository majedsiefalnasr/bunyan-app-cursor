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
    <div class="space-y-6">
        <UAlert color="info" variant="soft" :title="$t('projects.estimates_disclaimer')" />

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <template #header>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="text-sm font-medium text-[#171717] dark:text-white">{{
                        $t('projects.estimates_title')
                    }}</span>
                    <UButton size="sm" variant="soft" color="neutral" @click="addRow">
                        {{ $t('projects.estimates_add_line') }}
                    </UButton>
                </div>
            </template>

            <div v-if="lines.length === 0" class="text-sm text-[#666666]">
                {{ $t('projects.estimates_empty') }}
            </div>

            <div v-else class="space-y-4">
                <div
                    v-for="(row, index) in lines"
                    :key="row.id"
                    class="rounded-xl border border-default bg-elevated/25 p-4 dark:bg-elevated/10"
                >
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-12 sm:items-end">
                        <UFormField
                            class="min-w-0 w-full sm:col-span-6"
                            :label="$t('projects.estimates_col_label')"
                            :name="`estimate-${row.id}-label`"
                        >
                            <UInput v-model="row.label" class="w-full min-w-0" />
                        </UFormField>
                        <UFormField
                            class="min-w-0 w-full sm:col-span-2"
                            :label="$t('projects.estimates_col_qty')"
                            :name="`estimate-${row.id}-qty`"
                        >
                            <UInput
                                v-model.number="row.quantity"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full min-w-0"
                            />
                        </UFormField>
                        <UFormField
                            class="min-w-0 w-full sm:col-span-2"
                            :label="$t('projects.estimates_col_price')"
                            :name="`estimate-${row.id}-price`"
                        >
                            <UInput
                                v-model.number="row.unit_price"
                                type="number"
                                min="0"
                                step="0.01"
                                class="w-full min-w-0"
                            />
                        </UFormField>
                        <UFormField
                            class="min-w-0 w-full sm:col-span-2"
                            :label="$t('projects.estimates_col_actions')"
                            :name="`estimate-${row.id}-actions`"
                        >
                            <UButton
                                class="w-full justify-center font-medium"
                                color="error"
                                variant="soft"
                                icon="i-heroicons-trash"
                                :aria-label="$t('projects.estimates_remove_line', { n: index + 1 })"
                                @click="removeRow(row.id)"
                            >
                                {{ $t('projects.estimates_remove') }}
                            </UButton>
                        </UFormField>
                    </div>
                </div>
                <div
                    class="flex flex-col gap-2 border-t border-default pt-4 sm:flex-row sm:items-center sm:justify-between"
                >
                    <span class="text-sm text-[#666666]">{{ $t('projects.estimates_total') }}</span>
                    <span class="text-lg font-semibold tabular-nums text-[#171717] dark:text-white">
                        {{ total.toFixed(2) }}
                    </span>
                </div>
            </div>
        </UCard>
    </div>
</template>
