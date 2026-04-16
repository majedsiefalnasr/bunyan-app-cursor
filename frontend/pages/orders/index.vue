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
    const loadError = ref<string | null>(null);

    onMounted(async () => {
        try {
            const page = await listOrders({ per_page: 20 });
            rows.value = page.data;
        } catch (e: unknown) {
            rows.value = [];
            loadError.value = e instanceof Error ? e.message : String(e);
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-5xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                {{ $t('order.list_title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('order.list_subtitle') }}
            </p>
        </div>

        <div v-if="isLoading" class="space-y-4">
            <UPageGrid class="gap-4 sm:gap-6 lg:grid-cols-3">
                <UCard
                    v-for="i in 6"
                    :key="i"
                    class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]"
                >
                    <div class="space-y-3">
                        <USkeleton class="h-4 w-24" />
                        <USkeleton class="h-6 w-32" />
                        <USkeleton class="h-3 w-40" />
                        <USkeleton class="h-7 w-20 rounded-md" />
                    </div>
                </UCard>
            </UPageGrid>
        </div>

        <UAlert
            v-else-if="loadError"
            color="error"
            variant="soft"
            :title="$t('shell.error')"
            :description="loadError"
        />

        <p v-else-if="rows.length === 0" class="text-sm text-[#666666]">
            {{ $t('order.empty') }}
        </p>

        <UPageGrid v-else class="gap-4 sm:gap-6 lg:grid-cols-3">
            <UPageCard
                v-for="o in rows"
                :key="o.id"
                variant="subtle"
                :title="o.order_number ?? `#${o.id}`"
                :ui="{
                    container: 'gap-y-2',
                    title: 'font-medium text-[#171717] dark:text-white',
                }"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]"
            >
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <UBadge color="neutral" variant="soft" size="sm">
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
                        size="xs"
                        class="self-start"
                    >
                        {{ $t('order.view') }}
                    </UButton>
                </div>
            </UPageCard>
        </UPageGrid>
    </div>
</template>
