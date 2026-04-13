<script setup lang="ts">
    import type { RfqDetail, RfqItem } from '~/types/rfq';

    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['contractor'],
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const toast = useToast();
    const { t } = useI18n();
    const { getRfq, submitQuotation } = useRfqs();

    const id = computed(() => Number(route.params.id));
    const rfq = ref<RfqDetail | null>(null);
    const prices = ref<Record<number, number>>({});
    const loading = ref(true);
    const saving = ref(false);

    onMounted(async () => {
        try {
            rfq.value = await getRfq(id.value);
            const next: Record<number, number> = {};
            for (const it of rfq.value.items ?? []) {
                next[it.id] = next[it.id] ?? 0;
            }
            prices.value = next;
        } finally {
            loading.value = false;
        }
    });

    async function onSubmit() {
        if (!rfq.value?.items?.length) {
            return;
        }
        saving.value = true;
        try {
            const items = rfq.value.items.map((it: RfqItem) => ({
                rfq_item_id: it.id,
                unit_price: Number(prices.value[it.id] ?? 0),
            }));
            await submitQuotation(id.value, { items });
            toast.add({ title: t('rfq.submit_quote'), color: 'green' });
        } finally {
            saving.value = false;
        }
    }
</script>

<template>
    <div class="mx-auto max-w-2xl space-y-6">
        <UButton variant="soft" color="gray" :to="localePath('/contractor/rfqs')">
            {{ $t('rfq.back_list') }}
        </UButton>

        <div v-if="loading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <template v-else-if="rfq">
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.06em"
                >
                    {{ rfq.title }}
                </h1>
                <div class="mt-2">
                    <RfqStatusBadge :status="rfq.status" :status-label="rfq.status_label" />
                </div>
            </div>

            <UCard
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
            >
                <h2 class="mb-4 text-lg font-semibold text-[#171717] dark:text-white">
                    {{ $t('rfq.quote_heading') }}
                </h2>
                <form class="space-y-4" @submit.prevent="onSubmit">
                    <div v-for="it in rfq.items" :key="it.id" class="grid gap-2 sm:grid-cols-2">
                        <div>
                            <p class="text-sm font-medium text-[#171717] dark:text-white">
                                {{ it.description }}
                            </p>
                            <p class="text-xs text-[#666666]">{{ it.quantity }} {{ it.unit }}</p>
                        </div>
                        <UFormGroup :label="$t('rfq.unit_price')">
                            <UInput
                                v-model.number="prices[it.id]"
                                type="number"
                                min="0"
                                step="any"
                                required
                            />
                        </UFormGroup>
                    </div>
                    <UButton
                        type="submit"
                        color="primary"
                        :loading="saving"
                        :disabled="rfq.status !== 'quoting'"
                    >
                        {{ $t('rfq.submit_quote') }}
                    </UButton>
                </form>
            </UCard>
        </template>
    </div>
</template>
