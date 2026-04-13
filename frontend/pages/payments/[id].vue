<script setup lang="ts">
    import type { PaymentRow } from '~/composables/usePayments';

    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['customer', 'admin'],
    });

    const route = useRoute();
    const { getPayment } = usePayments();

    const payment = ref<PaymentRow | null>(null);
    const isLoading = ref(true);

    onMounted(async () => {
        try {
            const id = Number(route.params.id);
            const res = await getPayment(id);
            payment.value = res.data;
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-2xl space-y-6">
        <h1
            class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
            style="letter-spacing: -0.06em"
        >
            {{ $t('payments.detail_title') }}
        </h1>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('payments.loading') }}
        </div>

        <UCard
            v-else-if="payment"
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
        >
            <dl class="space-y-3 text-sm text-[#4d4d4d]">
                <div class="flex justify-between gap-4">
                    <dt>{{ $t('payments.amount') }}</dt>
                    <dd class="font-medium text-[#171717] dark:text-white">
                        {{ payment.amount }} {{ payment.currency }}
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt>{{ $t('payments.status') }}</dt>
                    <dd class="font-medium text-[#171717] dark:text-white">{{ payment.status }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt>{{ $t('payments.method') }}</dt>
                    <dd class="font-medium text-[#171717] dark:text-white">{{ payment.method }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt>{{ $t('payments.order') }}</dt>
                    <dd class="font-medium text-[#171717] dark:text-white">
                        #{{ payment.payable_id }}
                    </dd>
                </div>
            </dl>
        </UCard>
    </div>
</template>
