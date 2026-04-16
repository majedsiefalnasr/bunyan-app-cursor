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
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                {{ $t('payments.list_title') }}
            </h1>
            <div class="flex items-center gap-2">
                <UButton
                    :to="localePath('/orders')"
                    color="neutral"
                    variant="ghost"
                    icon="i-heroicons-arrow-right"
                    size="sm"
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

        <UPageGrid v-else class="gap-4 sm:gap-6 lg:grid-cols-3">
            <UPageCard
                v-for="p in rows"
                :key="p.id"
                variant="subtle"
                :title="`${$t('payments.order')} #${p.payable_id}`"
                :ui="{
                    container: 'gap-y-2',
                    title: 'font-medium text-[#171717] dark:text-white',
                }"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
            >
                <div class="space-y-2">
                    <p class="text-lg font-semibold text-[#171717] dark:text-white">
                        {{ p.amount }} {{ p.currency }}
                    </p>
                    <p class="text-sm text-[#666666]">
                        {{ $t('payments.status') }}: {{ p.status }}
                    </p>
                    <UButton
                        :to="localePath(`/payments/${p.id}`)"
                        variant="soft"
                        color="neutral"
                        size="xs"
                    >
                        {{ $t('payments.detail_title') }}
                    </UButton>
                </div>
            </UPageCard>
        </UPageGrid>

        <div class="flex flex-wrap gap-2">
            <UButton :to="localePath('/orders')" variant="outline" color="neutral" size="sm">
                {{ $t('payments.go_to_orders') }}
            </UButton>
            <UButton
                :to="localePath('/payments/checkout')"
                variant="outline"
                color="neutral"
                size="sm"
            >
                {{ $t('payments.checkout_title') }}
            </UButton>
        </div>
    </div>
</template>
