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
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.06em"
                >
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

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <p v-else-if="rows.length === 0" class="text-sm text-[#666666]">
            {{ $t('invoice.empty') }}
        </p>

        <div v-else class="grid gap-4 sm:grid-cols-2">
            <UCard
                v-for="inv in rows"
                :key="inv.id"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
                :ui="{ body: { padding: 'p-4 sm:p-5' } }"
            >
                <div class="flex flex-col gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm font-medium text-[#171717] dark:text-white">
                            {{ inv.invoice_number }}
                        </span>
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
                        size="sm"
                        class="self-start"
                    >
                        {{ $t('invoice.view') }}
                    </UButton>
                </div>
            </UCard>
        </div>
    </div>
</template>
