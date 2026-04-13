<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: ['auth'],
    });

    const localePath = useLocalePath();
    const { createInvoice } = useInvoices();
    const toast = useToast();

    const descriptionAr = ref('');
    const quantity = ref(1);
    const unitPrice = ref<number | undefined>(undefined);
    const loading = ref(false);

    async function onSubmit() {
        const price = unitPrice.value;
        if (price === undefined || price <= 0) {
            toast.add({ title: 'خطأ', description: 'أدخل سعراً صحيحاً', color: 'red' });
            return;
        }
        loading.value = true;
        try {
            const inv = await createInvoice({
                items: [
                    {
                        description_ar: descriptionAr.value || undefined,
                        quantity: quantity.value,
                        unit_price: price,
                    },
                ],
            });
            await navigateTo(localePath(`/invoices/${inv.id}`));
        } catch {
            toast.add({ title: 'خطأ', description: 'تعذر إنشاء الفاتورة', color: 'red' });
        } finally {
            loading.value = false;
        }
    }
</script>

<template>
    <div class="mx-auto max-w-lg space-y-6">
        <UButton variant="soft" color="gray" :to="localePath('/invoices')" size="sm">
            {{ $t('invoice.back') }}
        </UButton>

        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.06em"
            >
                {{ $t('invoice.create_title') }}
            </h1>
        </div>

        <UCard
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
            :ui="{ body: { padding: 'p-4 sm:p-5' } }"
        >
            <form class="space-y-4" @submit.prevent="onSubmit">
                <UFormGroup :label="$t('invoice.field_description')">
                    <UInput v-model="descriptionAr" />
                </UFormGroup>
                <UFormGroup :label="$t('invoice.field_quantity')">
                    <UInput v-model.number="quantity" type="number" min="1" />
                </UFormGroup>
                <UFormGroup :label="$t('invoice.field_unit_price')">
                    <UInput v-model.number="unitPrice" type="number" step="0.01" min="0" />
                </UFormGroup>
                <UButton type="submit" color="primary" block :loading="loading">
                    {{ $t('invoice.submit_create') }}
                </UButton>
            </form>
        </UCard>
    </div>
</template>
