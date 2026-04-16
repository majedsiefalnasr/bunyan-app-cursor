<script setup lang="ts">
    import type { PaymentRow } from '~/composables/usePayments';

    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['customer', 'admin'],
    });

    const route = useRoute();
    const { getPayment } = usePayments();

    const paymentIdParam = computed(() => route.query.paymentId ?? route.query.payment_id ?? null);
    const payment = ref<PaymentRow | null>(null);
    const isLoading = ref(false);
    const loadError = ref<string | null>(null);

    async function load() {
        loadError.value = null;
        payment.value = null;

        const raw = paymentIdParam.value;
        if (!raw) {
            return;
        }

        const id = Number(Array.isArray(raw) ? raw[0] : raw);
        if (!Number.isFinite(id) || id <= 0) {
            loadError.value = 'invalid_payment_id';
            return;
        }

        isLoading.value = true;
        try {
            const res = await getPayment(id);
            payment.value = res.data;
        } catch {
            loadError.value = 'payment_load_failed';
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(load);
    watch(paymentIdParam, () => void load());
</script>

<template>
    <div class="mx-auto max-w-2xl space-y-6">
        <div class="space-y-2">
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.06em"
            >
                {{ $t('payments.success_title') }}
            </h1>
            <p class="text-sm text-[#4d4d4d] dark:text-white/70">
                {{ $t('payments.success_subtitle') }}
            </p>
        </div>

        <UAlert
            v-if="loadError"
            color="error"
            variant="soft"
            :title="$t('payments.success_error_title')"
            :description="$t('payments.success_error_description')"
        />

        <div v-else-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('payments.loading') }}
        </div>

        <UCard
            v-else
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
        >
            <template #header>
                <div class="flex items-center justify-between gap-4">
                    <span class="text-sm font-medium text-[#171717] dark:text-white">
                        {{ $t('payments.success_details') }}
                    </span>
                    <UBadge color="success" variant="soft">
                        {{ $t('payments.success_badge') }}
                    </UBadge>
                </div>
            </template>

            <dl class="space-y-3 text-sm text-[#4d4d4d]">
                <div class="flex justify-between gap-4">
                    <dt>{{ $t('payments.status') }}</dt>
                    <dd class="font-medium text-[#171717] dark:text-white">
                        {{ payment?.status ?? '—' }}
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt>{{ $t('payments.amount') }}</dt>
                    <dd class="font-medium text-[#171717] dark:text-white">
                        <span v-if="payment">{{ payment.amount }} {{ payment.currency }}</span>
                        <span v-else>—</span>
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt>{{ $t('payments.method') }}</dt>
                    <dd class="font-medium text-[#171717] dark:text-white">
                        {{ payment?.method ?? '—' }}
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt>{{ $t('payments.order') }}</dt>
                    <dd class="font-medium text-[#171717] dark:text-white">
                        <span v-if="payment">#{{ payment.payable_id }}</span>
                        <span v-else>—</span>
                    </dd>
                </div>
            </dl>

            <template #footer>
                <div class="flex flex-wrap gap-2">
                    <UButton to="/payments" color="neutral" variant="soft">
                        {{ $t('payments.back_to_list') }}
                    </UButton>
                    <UButton to="/orders" color="neutral" variant="ghost">
                        {{ $t('payments.go_to_orders') }}
                    </UButton>
                </div>
            </template>
        </UCard>
    </div>
</template>
