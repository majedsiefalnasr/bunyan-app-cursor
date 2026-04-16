<script setup lang="ts">
    import type { RfqDetail } from '~/types/rfq';

    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['supplier', 'contractor', 'admin'],
    });

    const route = useRoute();
    const router = useRouter();
    const { t } = useI18n();
    const { getRfq, submitQuotation } = useRfqs();

    const rfq = ref<RfqDetail | null>(null);
    const isLoading = ref(true);
    const isSubmitting = ref(false);
    const errorMessage = ref<string | null>(null);

    type QuoteItem = { rfq_item_id: number; unit_price?: number; notes?: string };
    const form = reactive<{
        delivery_days?: number;
        notes: string;
        valid_until: string;
        items: QuoteItem[];
    }>({
        delivery_days: undefined,
        notes: '',
        valid_until: '',
        items: [],
    });

    function ensureQuoteItem(idx: number, rfqItemId: number): QuoteItem {
        const existing = form.items[idx];
        if (existing) return existing;
        const created: QuoteItem = { rfq_item_id: rfqItemId, unit_price: 0, notes: '' };
        form.items[idx] = created;
        return created;
    }

    async function load() {
        errorMessage.value = null;
        isLoading.value = true;
        try {
            const id = Number(route.params.id);
            rfq.value = await getRfq(id);
            form.items =
                rfq.value.items?.map((it) => ({
                    rfq_item_id: it.id,
                    unit_price: undefined,
                    notes: '',
                })) ?? [];
        } catch {
            errorMessage.value = t('rfq.error_load_failed');
        } finally {
            isLoading.value = false;
        }
    }

    async function onSubmit() {
        if (!rfq.value) {
            return;
        }
        errorMessage.value = null;

        const missing = form.items.some((it) => it.unit_price === undefined || it.unit_price <= 0);
        if (missing) {
            errorMessage.value = t('rfq.error_missing_unit_price');
            return;
        }

        isSubmitting.value = true;
        try {
            await submitQuotation(rfq.value.id, {
                items: form.items.map((it) => ({
                    rfq_item_id: it.rfq_item_id,
                    unit_price: Number(it.unit_price),
                    notes: it.notes ? it.notes : null,
                })),
                delivery_days: form.delivery_days ?? null,
                notes: form.notes ? form.notes : null,
                valid_until: form.valid_until ? form.valid_until : null,
            });
            await router.push(`/rfqs/${rfq.value.id}`);
        } catch {
            errorMessage.value = t('rfq.error_submit_failed');
        } finally {
            isSubmitting.value = false;
        }
    }

    onMounted(load);
</script>

<template>
    <div class="space-y-6">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                {{ $t('rfq.quote_heading') }}
            </h1>
            <p class="text-sm text-[#4d4d4d] dark:text-white/70">
                {{ rfq?.title ?? '' }}
            </p>
        </div>

        <UAlert
            v-if="errorMessage"
            color="error"
            variant="soft"
            :title="$t('rfq.error_title')"
            :description="errorMessage"
        />

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <UCard
            v-else-if="rfq"
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
        >
            <div class="space-y-4">
                <div class="grid gap-4 md:grid-cols-3">
                    <UFormGroup :label="$t('rfq.field_deadline')">
                        <UInput v-model="form.valid_until" type="date" />
                    </UFormGroup>
                    <UFormGroup :label="$t('rfq.quote_delivery_days')">
                        <UInput v-model.number="form.delivery_days" type="number" min="0" />
                    </UFormGroup>
                    <UFormGroup :label="$t('rfq.quote_notes')">
                        <UInput v-model="form.notes" />
                    </UFormGroup>
                </div>

                <div class="space-y-2">
                    <h2 class="text-sm font-medium text-[#171717] dark:text-white">
                        {{ $t('rfq.items_heading') }}
                    </h2>

                    <div class="overflow-auto rounded-lg shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                        <table class="min-w-full text-sm">
                            <thead class="bg-[#fafafa] text-[#171717]">
                                <tr>
                                    <th class="px-3 py-2 text-right">
                                        {{ $t('rfq.item_description') }}
                                    </th>
                                    <th class="px-3 py-2 text-right">{{ $t('rfq.item_qty') }}</th>
                                    <th class="px-3 py-2 text-right">{{ $t('rfq.item_unit') }}</th>
                                    <th class="px-3 py-2 text-right">{{ $t('rfq.unit_price') }}</th>
                                    <th class="px-3 py-2 text-right">
                                        {{ $t('rfq.quote_item_notes') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(it, idx) in rfq.items ?? []"
                                    :key="it.id"
                                    class="border-t border-[#ebebeb]"
                                >
                                    <td class="px-3 py-2">{{ it.description }}</td>
                                    <td class="px-3 py-2">{{ it.quantity }}</td>
                                    <td class="px-3 py-2">{{ it.unit }}</td>
                                    <td class="px-3 py-2">
                                        <UInput
                                            :model-value="String(form.items[idx]?.unit_price ?? '')"
                                            type="number"
                                            min="0"
                                            @update:model-value="
                                                (v) => {
                                                    const row = ensureQuoteItem(idx, it.id);
                                                    row.unit_price = v === '' ? 0 : Number(v);
                                                }
                                            "
                                        />
                                    </td>
                                    <td class="px-3 py-2">
                                        <UInput
                                            :model-value="form.items[idx]?.notes ?? ''"
                                            @update:model-value="
                                                (v) => {
                                                    const row = ensureQuoteItem(idx, it.id);
                                                    row.notes = v;
                                                }
                                            "
                                        />
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <UButton :loading="isSubmitting" size="sm" @click="onSubmit">
                        {{ $t('rfq.submit_quote') }}
                    </UButton>
                    <UButton color="neutral" variant="soft" size="sm" to="/rfqs">
                        {{ $t('rfq.back_list') }}
                    </UButton>
                </div>
            </div>
        </UCard>
    </div>
</template>
