<script setup lang="ts">
    import type { CompareCells, QuotationRow, RfqItem } from '~/types/rfq';

    const props = withDefaults(
        defineProps<{
            items: RfqItem[];
            quotations: QuotationRow[];
            cells: CompareCells;
            /** Document direction for logical layout (RTL for Arabic). */
            dir?: 'rtl' | 'ltr';
        }>(),
        {
            dir: 'rtl',
        }
    );

    const { t } = useI18n();

    function supplierTitle(q: QuotationRow): string {
        const ar = q.supplier_profile?.company_name_ar;
        const en = q.supplier_profile?.company_name_en;
        return ar || en || t('rfq.compare_unknown_supplier');
    }

    function cellFor(quotationId: number, itemId: number) {
        const byQ = props.cells[String(quotationId)];
        if (!byQ) {
            return null;
        }
        return byQ[String(itemId)] ?? null;
    }
</script>

<template>
    <div
        class="overflow-x-auto rounded-lg bg-white shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:bg-[#171717] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
        :dir="dir"
        data-testid="quotation-compare-wrap"
    >
        <table class="min-w-full text-start text-sm text-[#171717] dark:text-white">
            <thead>
                <tr class="bg-[#fafafa] dark:bg-[#0f0f0f]">
                    <th class="px-3 py-2 font-medium text-[#4d4d4d] dark:text-[#ebebeb]">
                        {{ t('rfq.compare_item') }}
                    </th>
                    <th
                        v-for="q in quotations"
                        :key="q.id"
                        class="whitespace-nowrap px-3 py-2 font-medium text-[#4d4d4d] dark:text-[#ebebeb]"
                    >
                        {{ supplierTitle(q) }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="item in items"
                    :key="item.id"
                    class="shadow-[0px_1px_0px_0px_rgba(0,0,0,0.06)] dark:shadow-[0px_1px_0px_0px_rgba(255,255,255,0.06)]"
                >
                    <td class="max-w-xs px-3 py-2 align-top">
                        <div class="font-medium">{{ item.description }}</div>
                        <div class="mt-0.5 text-xs text-[#666666]">
                            {{ item.quantity }} {{ item.unit }}
                        </div>
                    </td>
                    <td
                        v-for="q in quotations"
                        :key="`${q.id}-${item.id}`"
                        class="px-3 py-2 align-top"
                    >
                        <template v-if="cellFor(q.id, item.id)">
                            <div class="font-medium tabular-nums">
                                {{ cellFor(q.id, item.id)?.unit_price }}
                            </div>
                            <div class="text-xs text-[#666666] tabular-nums">
                                {{ cellFor(q.id, item.id)?.total_price }}
                            </div>
                        </template>
                        <span v-else class="text-[#808080]">—</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
