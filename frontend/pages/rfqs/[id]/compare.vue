<script setup lang="ts">
    import type { ComparePayload } from '~/types/rfq';

    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['customer'],
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const { direction } = useDirection();
    const { compareRfq } = useRfqs();

    const id = computed(() => Number(route.params.id));
    const payload = ref<ComparePayload | null>(null);
    const loading = ref(true);

    onMounted(async () => {
        try {
            payload.value = await compareRfq(id.value);
        } finally {
            loading.value = false;
        }
    });
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                    {{ $t('rfq.compare_title') }}
                </h1>
                <p class="mt-1 text-sm text-[#666666]">
                    {{ $t('rfq.compare_subtitle') }}
                </p>
            </div>
            <UButton variant="soft" color="neutral" size="sm" :to="localePath(`/rfqs/${id}`)">
                {{ $t('rfq.detail_link') }}
            </UButton>
        </div>

        <div v-if="loading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <QuotationComparisonTable
            v-else-if="payload && payload.items.length && payload.quotations.length"
            :items="payload.items"
            :quotations="payload.quotations"
            :cells="payload.cells"
            :dir="direction === 'rtl' ? 'rtl' : 'ltr'"
        />

        <p v-else-if="payload" class="text-sm text-[#666666]">
            {{ $t('rfq.compare_empty') }}
        </p>
    </div>
</template>
