<script setup lang="ts">
    import type { PaymentRow } from '~/composables/usePayments';

    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['customer', 'admin'],
    });

    const localePath = useLocalePath();
    const { listHistory } = usePayments();

    const rows = ref<PaymentRow[]>([]);
    const isLoading = ref(true);

    onMounted(async () => {
        try {
            const page = await listHistory(20);
            rows.value = page.data;
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.06em"
            >
                {{ $t('payments.list_title') }}
            </h1>
            <div class="flex items-center gap-2">
                <UButton
                    :to="localePath('/orders')"
                    color="gray"
                    variant="ghost"
                    icon="i-heroicons-arrow-right"
                >
                    {{ $t('payments.go_to_orders') }}
                </UButton>
            </div>
        </div>
        <p class="text-sm text-[#666666]">
            {{ $t('payments.list_subtitle') }}
        </p>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('payments.loading') }}
        </div>

        <p v-else-if="rows.length === 0" class="text-sm text-[#666666]">
            {{ $t('payments.empty') }}
        </p>

        <div v-else class="grid gap-4 sm:grid-cols-2">
            <UCard
                v-for="p in rows"
                :key="p.id"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
            >
                <div class="space-y-2">
                    <p class="text-sm text-[#4d4d4d]">
                        {{ $t('payments.order') }} #{{ p.payable_id }}
                    </p>
                    <p class="text-lg font-semibold text-[#171717] dark:text-white">
                        {{ p.amount }} {{ p.currency }}
                    </p>
                    <p class="text-sm text-[#666666]">
                        {{ $t('payments.status') }}: {{ p.status }}
                    </p>
                    <UButton :to="localePath(`/payments/${p.id}`)" variant="soft" color="gray">
                        {{ $t('payments.detail_title') }}
                    </UButton>
                </div>
            </UCard>
        </div>

        <div class="flex flex-wrap gap-2">
            <UButton :to="localePath('/orders')" variant="outline" color="gray">
                {{ $t('payments.go_to_orders') }}
            </UButton>
            <UButton :to="localePath('/payments/checkout')" variant="outline" color="gray">
                {{ $t('payments.checkout_title') }}
            </UButton>
        </div>
    </div>
</template>
