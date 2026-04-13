<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: ['auth'],
    });

    const localePath = useLocalePath();
    const { listOrders } = useOrders();

    const rows = ref<
        Array<{
            id: number;
            order_number: string | null;
            status: string;
            total_price: string;
            created_at: string | null;
        }>
    >([]);
    const isLoading = ref(true);

    onMounted(async () => {
        try {
            const page = await listOrders({ per_page: 20 });
            rows.value = page.data;
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.06em"
                >
                    {{ $t('order.list_title') }}
                </h1>
                <p class="mt-1 text-sm text-[#666666]">
                    {{ $t('order.list_subtitle') }}
                </p>
            </div>
        </div>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <p v-else-if="rows.length === 0" class="text-sm text-[#666666]">
            {{ $t('order.empty') }}
        </p>

        <div v-else class="grid gap-4 sm:grid-cols-2">
            <UCard
                v-for="o in rows"
                :key="o.id"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
                :ui="{ body: { padding: 'p-4 sm:p-5' } }"
            >
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-medium text-[#171717] dark:text-white">
                            {{ o.order_number ?? `#${o.id}` }}
                        </span>
                        <UBadge color="gray" variant="soft" size="sm">
                            {{ o.status }}
                        </UBadge>
                    </div>
                    <p class="text-sm text-[#666666]">
                        {{ $t('order.total') }}: {{ o.total_price }}
                    </p>
                    <UButton
                        :to="localePath(`/orders/${o.id}`)"
                        color="primary"
                        variant="soft"
                        size="sm"
                        class="self-start"
                    >
                        {{ $t('order.view') }}
                    </UButton>
                </div>
            </UCard>
        </div>
    </div>
</template>
