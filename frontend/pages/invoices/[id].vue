<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: ['auth'],
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const toast = useToast();
    const { getInvoice, sendInvoice, downloadInvoicePdf } = useInvoices();

    const id = computed(() => Number(route.params.id));
    const invoice = ref<Awaited<ReturnType<typeof getInvoice>> | null>(null);
    const isLoading = ref(true);
    const isSending = ref(false);
    const isPdf = ref(false);

    async function load() {
        isLoading.value = true;
        try {
            invoice.value = await getInvoice(id.value);
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(load);

    async function onSend() {
        if (!invoice.value) {
            return;
        }
        isSending.value = true;
        try {
            invoice.value = await sendInvoice(invoice.value.id);
            toast.add({ title: 'تم', description: 'تم إرسال الفاتورة', color: 'green' });
        } catch {
            toast.add({ title: 'خطأ', description: 'تعذر الإرسال', color: 'red' });
        } finally {
            isSending.value = false;
        }
    }

    async function onPdf() {
        if (!invoice.value) {
            return;
        }
        isPdf.value = true;
        try {
            await downloadInvoicePdf(invoice.value.id, `${invoice.value.invoice_number}.pdf`);
        } catch {
            toast.add({ title: 'خطأ', description: 'تعذر تنزيل PDF', color: 'red' });
        } finally {
            isPdf.value = false;
        }
    }
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <UButton variant="soft" color="gray" :to="localePath('/invoices')" size="sm">
            {{ $t('invoice.back') }}
        </UButton>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <template v-else-if="invoice">
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.06em"
                >
                    {{ invoice.invoice_number }}
                </h1>
                <p class="mt-1 text-sm text-[#666666]">
                    {{ invoice.status_label }} — {{ $t('invoice.due') }}:
                    {{ invoice.due_date ?? '—' }}
                </p>
            </div>

            <UCard
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
                :ui="{ body: { padding: 'p-4 sm:p-5' } }"
            >
                <h2 class="mb-3 text-sm font-medium text-[#171717] dark:text-white">
                    {{ $t('invoice.items') }}
                </h2>
                <ul class="space-y-2 text-sm text-[#4d4d4d]">
                    <li
                        v-for="it in invoice.items || []"
                        :key="it.id"
                        class="flex justify-between gap-2"
                    >
                        <span>{{ it.description_ar || it.description_en || '—' }}</span>
                        <span>{{ it.line_total }}</span>
                    </li>
                </ul>
                <div class="mt-4 space-y-1 border-t border-[#ebebeb] pt-4 text-sm">
                    <div class="flex justify-between">
                        <span>{{ $t('invoice.subtotal') }}</span>
                        <span>{{ invoice.subtotal }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>{{ $t('invoice.vat') }} ({{ invoice.vat_percentage }}%)</span>
                        <span>{{ invoice.vat_amount }}</span>
                    </div>
                    <div class="flex justify-between font-medium text-[#171717] dark:text-white">
                        <span>{{ $t('invoice.total') }}</span>
                        <span>{{ invoice.total }}</span>
                    </div>
                </div>
            </UCard>

            <UCard
                v-if="invoice.zatca_qr_data"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
                :ui="{ body: { padding: 'p-4 sm:p-5' } }"
            >
                <h2 class="mb-2 text-sm font-medium text-[#171717] dark:text-white">
                    {{ $t('invoice.zatca_payload') }}
                </h2>
                <p class="break-all font-mono text-xs text-[#4d4d4d]">
                    {{ invoice.zatca_qr_data }}
                </p>
                <p class="mt-2 text-xs text-[#666666]">
                    {{ $t('invoice.zatca_hint') }}
                </p>
            </UCard>

            <div class="flex flex-wrap gap-2">
                <UButton color="primary" :loading="isPdf" @click="onPdf">
                    {{ $t('invoice.download_pdf') }}
                </UButton>
                <UButton color="gray" variant="soft" :loading="isSending" @click="onSend">
                    {{ $t('invoice.send_email') }}
                </UButton>
            </div>
        </template>
    </div>
</template>
