<script setup lang="ts">
    interface InventoryRow {
        id: number;
        product_id: number;
        variant_id: number | null;
        warehouse_location: string;
        quantity: number;
        reserved_quantity: number;
        min_quantity: number;
        available_quantity: number;
        product?: { id: number; name: string };
    }

    definePageMeta({
        layout: 'admin',
        middleware: ['auth', 'role'],
        requiresAuth: true,
        roles: ['admin'],
    });

    const { apiFetch } = useApi();
    const toast = useToast();

    const rows = ref<InventoryRow[]>([]);
    const isLoading = ref(true);
    const adjustOpen = ref(false);
    const selected = ref<InventoryRow | null>(null);
    const delta = ref<number>(0);
    const notes = ref('');

    function unwrapList(payload: unknown): InventoryRow[] {
        if (payload === null || payload === undefined) {
            return [];
        }
        if (Array.isArray(payload)) {
            return payload as InventoryRow[];
        }
        if (typeof payload === 'object' && 'data' in (payload as object)) {
            const inner = (payload as { data?: unknown }).data;
            return Array.isArray(inner) ? (inner as InventoryRow[]) : [];
        }

        return [];
    }

    async function load() {
        isLoading.value = true;
        try {
            const res = await apiFetch<{ data: unknown }>('/v1/inventory');
            rows.value = unwrapList(res.data);
        } catch {
            toast.add({
                title: 'خطأ',
                description: 'تعذر تحميل المخزون',
                color: 'red',
            });
        } finally {
            isLoading.value = false;
        }
    }

    function openAdjust(row: InventoryRow) {
        selected.value = row;
        delta.value = 0;
        notes.value = '';
        adjustOpen.value = true;
    }

    async function submitAdjust() {
        if (!selected.value || delta.value === 0) {
            return;
        }
        try {
            await apiFetch(`/v1/inventory/${selected.value.product_id}/adjust`, {
                method: 'PUT',
                body: {
                    quantity_delta: delta.value,
                    notes: notes.value || undefined,
                },
            });
            toast.add({ title: 'تم', description: 'تم تحديث المخزون', color: 'green' });
            adjustOpen.value = false;
            await load();
        } catch {
            toast.add({ title: 'خطأ', description: 'فشل تعديل المخزون', color: 'red' });
        }
    }

    const columns = [
        { key: 'product', label: 'المنتج' },
        { key: 'warehouse_location', label: 'الموقع' },
        { key: 'quantity', label: 'الكمية' },
        { key: 'available_quantity', label: 'المتاح' },
        { key: 'min_quantity', label: 'الحد الأدنى' },
        { key: 'actions', label: '' },
    ];

    onMounted(load);
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6">
        <div
            class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between rtl:flex-row-reverse"
        >
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                إدارة المخزون
            </h1>
            <UButton color="gray" variant="soft" :loading="isLoading" @click="load">تحديث</UButton>
        </div>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]">
            <div v-if="isLoading" class="text-sm text-[#666666]">جاري التحميل…</div>
            <UTable v-else :rows="rows" :columns="columns">
                <template #product-data="{ row }">
                    <span class="text-sm text-[#171717]">{{
                        row.product?.name ?? `#${row.product_id}`
                    }}</span>
                </template>
                <template #actions-data="{ row }">
                    <UButton size="xs" @click="openAdjust(row)">تعديل</UButton>
                </template>
            </UTable>
        </UCard>

        <UModal v-model="adjustOpen">
            <UCard>
                <template #header>
                    <span class="font-medium">تعديل الكمية</span>
                </template>
                <div v-if="selected" class="space-y-4">
                    <p class="text-sm text-[#4d4d4d]">
                        {{ selected.product?.name ?? `منتج #${selected.product_id}` }}
                    </p>
                    <UFormGroup label="التغيير (+ أو -)">
                        <UInput v-model.number="delta" type="number" />
                    </UFormGroup>
                    <UFormGroup label="ملاحظات (اختياري)">
                        <UTextarea v-model="notes" />
                    </UFormGroup>
                    <div class="flex justify-end gap-2 rtl:flex-row-reverse">
                        <UButton variant="soft" color="gray" @click="adjustOpen = false"
                            >إلغاء</UButton
                        >
                        <UButton :disabled="delta === 0" @click="submitAdjust">حفظ</UButton>
                    </div>
                </div>
            </UCard>
        </UModal>
    </div>
</template>
