<script setup lang="ts">
    definePageMeta({
        layout: 'admin',
        middleware: ['auth', 'role'],
        roles: ['admin'],
    });

    interface TierRow {
        min_quantity: number;
        max_quantity: number | null;
        unit_price: string;
        product_variant_id: number | null;
    }

    function normalizeTiersForApi(rows: TierRow[]) {
        return rows.map((row) => ({
            min_quantity: Number(row.min_quantity),
            max_quantity:
                row.max_quantity === null || row.max_quantity === undefined
                    ? null
                    : Number(row.max_quantity),
            unit_price: row.unit_price,
            product_variant_id:
                row.product_variant_id === null || row.product_variant_id === undefined
                    ? null
                    : Number(row.product_variant_id),
        }));
    }

    const route = useRoute();
    const { apiFetch } = useApi();
    const toast = useToast();

    const tiers = ref<TierRow[]>([]);
    const isLoading = ref(true);
    const isSaving = ref(false);

    async function load() {
        isLoading.value = true;
        try {
            const id = route.params.id;
            const res = await apiFetch<{ data: { tiers: TierRow[] } }>(
                `/v1/products/${id}/pricing`
            );
            const list = res.data?.tiers ?? [];
            tiers.value = list.map((t) => ({
                min_quantity: t.min_quantity,
                max_quantity: t.max_quantity,
                unit_price: String(t.unit_price),
                product_variant_id: t.product_variant_id,
            }));
            if (tiers.value.length === 0) {
                tiers.value.push({
                    min_quantity: 1,
                    max_quantity: null,
                    unit_price: '0.00',
                    product_variant_id: null,
                });
            }
        } catch {
            toast.add({ title: 'خطأ', description: 'تعذر تحميل التسعير', color: 'error' });
            tiers.value = [];
        } finally {
            isLoading.value = false;
        }
    }

    async function save() {
        isSaving.value = true;
        try {
            const id = route.params.id;
            await apiFetch(`/v1/admin/products/${id}/pricing`, {
                method: 'PUT',
                body: { tiers: normalizeTiersForApi(tiers.value) },
            });
            toast.add({ title: 'تم', description: 'تم حفظ شرائح السعر', color: 'success' });
            await load();
        } catch {
            toast.add({ title: 'خطأ', description: 'تعذر حفظ التسعير', color: 'error' });
        } finally {
            isSaving.value = false;
        }
    }

    function addRow() {
        tiers.value.push({
            min_quantity: 1,
            max_quantity: null,
            unit_price: '0.00',
            product_variant_id: null,
        });
    }

    watch(
        () => route.params.id,
        () => {
            void load();
        },
        { immediate: true }
    );
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-white" style="letter-spacing: -0.06em">
                {{ $t('catalog.admin_pricing_title') }}
            </h1>
            <p class="mt-1 text-sm text-slate-300">{{ $t('catalog.admin_pricing_subtitle') }}</p>
        </div>

        <div v-if="isLoading" class="text-sm text-slate-300">{{ $t('shell.loading') }}</div>

        <UCard v-else class="bg-slate-800 ring-1 ring-slate-700">
            <div class="space-y-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm text-slate-100">
                        <thead>
                            <tr class="border-b border-slate-600 text-left">
                                <th class="py-2 pe-4">{{ $t('catalog.tier_min') }}</th>
                                <th class="py-2 pe-4">{{ $t('catalog.tier_max') }}</th>
                                <th class="py-2 pe-4">{{ $t('catalog.tier_unit') }}</th>
                                <th class="py-2 pe-4">{{ $t('catalog.tier_variant') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="(row, idx) in tiers"
                                :key="idx"
                                class="border-b border-slate-700"
                            >
                                <td class="py-2 pe-4">
                                    <UInput
                                        v-model.number="row.min_quantity"
                                        type="number"
                                        min="1"
                                        size="sm"
                                    />
                                </td>
                                <td class="py-2 pe-4">
                                    <UInput
                                        :model-value="
                                            row.max_quantity === null
                                                ? ''
                                                : String(row.max_quantity)
                                        "
                                        type="number"
                                        min="1"
                                        size="sm"
                                        :placeholder="$t('catalog.tier_max_open')"
                                        @update:model-value="
                                            (v) => {
                                                const raw = typeof v === 'number' ? String(v) : v;
                                                row.max_quantity =
                                                    raw === '' || raw === undefined
                                                        ? null
                                                        : Number(raw);
                                            }
                                        "
                                    />
                                </td>
                                <td class="py-2 pe-4">
                                    <UInput v-model="row.unit_price" type="text" size="sm" />
                                </td>
                                <td class="py-2 pe-4">
                                    <UInput
                                        :model-value="
                                            row.product_variant_id === null
                                                ? ''
                                                : String(row.product_variant_id)
                                        "
                                        type="number"
                                        size="sm"
                                        :placeholder="$t('catalog.tier_variant_null')"
                                        @update:model-value="
                                            (v) => {
                                                const raw = typeof v === 'number' ? String(v) : v;
                                                row.product_variant_id =
                                                    raw === '' || raw === undefined
                                                        ? null
                                                        : Number(raw);
                                            }
                                        "
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-wrap gap-2">
                    <UButton color="neutral" variant="soft" @click="addRow">
                        {{ $t('catalog.tier_add') }}
                    </UButton>
                    <UButton :loading="isSaving" @click="save">
                        {{ $t('catalog.tier_save') }}
                    </UButton>
                </div>
            </div>
        </UCard>
    </div>
</template>
