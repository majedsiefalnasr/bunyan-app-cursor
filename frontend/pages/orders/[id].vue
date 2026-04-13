<script setup lang="ts">
    import type { OrderDetail } from '~/composables/useOrders';

    definePageMeta({
        layout: 'default',
        middleware: ['auth'],
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const { t } = useI18n();
    const { getOrder, confirmOrder, cancelOrder } = useOrders();

    const orderId = computed(() => Number(route.params.id));
    const order = ref<OrderDetail | null>(null);
    const isLoading = ref(true);
    const actionLoading = ref(false);
    const toast = useToast();

    async function load() {
        isLoading.value = true;
        try {
            order.value = await getOrder(orderId.value);
        } catch {
            order.value = null;
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(load);

    async function onConfirm() {
        if (!order.value) {
            return;
        }
        actionLoading.value = true;
        try {
            order.value = await confirmOrder(order.value.id);
            toast.add({ title: t('order.confirm_ok'), color: 'green' });
        } catch {
            toast.add({ title: t('order.action_failed'), color: 'red' });
        } finally {
            actionLoading.value = false;
        }
    }

    async function onCancel() {
        if (!order.value) {
            return;
        }
        actionLoading.value = true;
        try {
            order.value = await cancelOrder(order.value.id);
            toast.add({ title: t('order.cancel_ok'), color: 'green' });
        } catch {
            toast.add({ title: t('order.action_failed'), color: 'red' });
        } finally {
            actionLoading.value = false;
        }
    }
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <UButton variant="soft" color="gray" :to="localePath('/orders')" size="sm">
            {{ $t('order.back') }}
        </UButton>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <div v-else-if="!order" class="text-sm text-[#666666]">
            {{ $t('order.not_found') }}
        </div>

        <template v-else>
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.06em"
                >
                    {{ order.order_number ?? `#${order.id}` }}
                </h1>
                <p class="mt-1 text-sm text-[#666666]">
                    {{ $t('order.status') }}: {{ order.status }}
                </p>
            </div>

            <UCard
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
                :ui="{ body: { padding: 'p-4 sm:p-5' } }"
            >
                <div class="space-y-2 text-sm text-[#171717] dark:text-white">
                    <p>{{ $t('order.total') }}: {{ order.total_price }}</p>
                    <p v-if="order.confirmed_at">
                        {{ $t('order.confirmed_at') }}: {{ order.confirmed_at }}
                    </p>
                    <p v-if="order.shipped_at">
                        {{ $t('order.shipped_at') }}: {{ order.shipped_at }}
                    </p>
                    <p v-if="order.delivered_at">
                        {{ $t('order.delivered_at') }}: {{ order.delivered_at }}
                    </p>
                </div>
            </UCard>

            <div v-if="order.items?.length" class="space-y-2">
                <h2 class="text-sm font-semibold text-[#171717] dark:text-white">
                    {{ $t('order.items') }}
                </h2>
                <ul class="space-y-1 text-sm text-[#666666]">
                    <li v-for="it in order.items" :key="it.id">
                        #{{ it.product_id }} × {{ it.quantity }} — {{ it.price }}
                    </li>
                </ul>
            </div>

            <div v-if="order.status === 'pending'" class="flex flex-wrap gap-2">
                <UButton color="primary" :loading="actionLoading" @click="onConfirm">
                    {{ $t('order.confirm') }}
                </UButton>
                <UButton color="gray" variant="soft" :loading="actionLoading" @click="onCancel">
                    {{ $t('order.cancel') }}
                </UButton>
            </div>
        </template>
    </div>
</template>
