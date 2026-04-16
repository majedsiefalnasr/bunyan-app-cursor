<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['customer'],
    });

    const localePath = useLocalePath();
    const cart = useCartStore();
    const { apiFetch } = useApi();
    const toast = useToast();
    const { t } = useI18n();

    const isSubmitting = ref(false);

    const total = computed(() => cart.total);

    async function checkout() {
        if (cart.lines.length === 0) return;
        isSubmitting.value = true;
        try {
            const res = await apiFetch<{ data: { id: number } }>('/v1/orders', {
                method: 'POST',
                body: {
                    items: cart.lines.map((l) => ({
                        product_id: l.product_id,
                        quantity: l.quantity,
                        price: Number(l.price),
                    })),
                },
            });

            const orderId = res.data?.id;
            cart.clear();
            toast.add({ title: t('order.created_success'), color: 'success' });
            await navigateTo(localePath(`/payments/checkout?orderId=${orderId}`));
        } catch {
            /* useApi surfaces toast */
        } finally {
            isSubmitting.value = false;
        }
    }
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                {{ $t('cart.title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('cart.subtitle') }}
            </p>
        </div>

        <UAlert
            v-if="cart.lines.length === 0"
            variant="soft"
            :title="$t('cart.empty')"
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
        >
            <template #description>
                <UButton
                    :to="localePath('/products')"
                    color="primary"
                    variant="soft"
                    size="sm"
                    class="mt-3"
                >
                    {{ $t('cart.continue_shopping') }}
                </UButton>
            </template>
        </UAlert>

        <div v-else class="space-y-4">
            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <div class="p-4">
                    <ul class="divide-y divide-[#ebebeb] dark:divide-[#262626]">
                        <li v-for="l in cart.lines" :key="l.product_id" class="py-4">
                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div class="min-w-0">
                                    <div
                                        class="truncate font-medium text-[#171717] dark:text-white"
                                    >
                                        {{ l.name }}
                                    </div>
                                    <div class="mt-1 text-sm text-[#666666]">
                                        {{ $t('catalog.price_label') }}: {{ l.price }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-[#666666]">{{
                                            $t('cart.quantity')
                                        }}</span>
                                        <UInput
                                            :model-value="l.quantity"
                                            type="number"
                                            min="1"
                                            class="w-24"
                                            @update:model-value="
                                                (v) => cart.setQuantity(l.product_id, Number(v))
                                            "
                                        />
                                    </div>
                                    <UButton
                                        color="error"
                                        variant="ghost"
                                        icon="i-heroicons-trash"
                                        @click="cart.remove(l.product_id)"
                                    >
                                        {{ $t('cart.remove') }}
                                    </UButton>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </UCard>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-sm text-[#666666]">
                    {{ $t('cart.total') }}:
                    <span class="font-medium text-[#171717] dark:text-white">{{
                        total.toFixed(2)
                    }}</span>
                </div>
                <UButton
                    color="primary"
                    variant="solid"
                    size="sm"
                    :loading="isSubmitting"
                    @click="checkout"
                >
                    {{ $t('cart.checkout') }}
                </UButton>
            </div>
        </div>
    </div>
</template>
