<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: ['auth'],
    });

    const localePath = useLocalePath();
    const { listInvoices } = useInvoices();

    const rows = ref<
        Array<{
            id: number;
            invoice_number: string;
            status: string;
            status_label: string;
            total: string;
            due_date: string | null;
            created_at: string | null;
        }>
    >([]);
    const isLoading = ref(true);

    onMounted(async () => {
        try {
            const page = await listInvoices({ per_page: 20 });
            rows.value = page.data;
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                    {{ $t('invoice.list_title') }}
                </h1>
                <p class="mt-1 text-sm text-[#666666]">
                    {{ $t('invoice.list_subtitle') }}
                </p>
            </div>
            <UButton :to="localePath('/invoices/create')" color="primary" size="sm">
                {{ $t('invoice.create') }}
            </UButton>
        </div>

        <div v-if="isLoading" class="space-y-4">
            <UPageGrid class="gap-4 sm:gap-6 lg:grid-cols-3">
                <UCard
                    v-for="i in 6"
                    :key="i"
                    class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]"
                >
                    <div class="space-y-3">
                        <USkeleton class="h-4 w-28" />
                        <USkeleton class="h-6 w-40" />
                        <USkeleton class="h-3 w-44" />
                        <USkeleton class="h-7 w-20 rounded-md" />
                    </div>
                </UCard>
            </UPageGrid>
        </div>

        <p v-else-if="rows.length === 0" class="text-sm text-[#666666]">
            {{ $t('invoice.empty') }}
        </p>

        <UPageGrid v-else class="gap-4 sm:gap-6 lg:grid-cols-3">
            <UPageCard
                v-for="inv in rows"
                :key="inv.id"
                variant="subtle"
                :title="inv.invoice_number"
                :ui="{
                    container: 'gap-y-2',
                    title: 'font-medium text-[#171717] dark:text-white',
                }"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]"
            >
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <UBadge color="gray" variant="soft" size="sm">
                            {{ inv.status_label }}
                        </UBadge>
                    </div>
                    <p class="text-sm text-[#666666]">
                        {{ $t('invoice.total') }}: {{ inv.total }} {{ $t('invoice.currency') }}
                    </p>
                    <UButton
                        :to="localePath(`/invoices/${inv.id}`)"
                        color="primary"
                        variant="soft"
                        size="xs"
                        class="self-start"
                    >
                        {{ $t('invoice.view') }}
                    </UButton>
                </div>
            </UPageCard>
        </UPageGrid>
    </div>
</template>
