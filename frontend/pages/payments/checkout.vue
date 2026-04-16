<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['customer', 'admin'],
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const { t } = useI18n();
    const { initiate } = usePayments();
    const toast = useToast();

    const orderId = computed(() => Number(route.query.orderId || 0));
    const hasOrderId = computed(() => Number.isFinite(orderId.value) && orderId.value > 0);
    const method = ref<'mada' | 'card' | 'bank_transfer'>('mada');
    const isSubmitting = ref(false);

    async function submit() {
        if (!hasOrderId.value) {
            toast.add({
                title: t('errors.codes.VALIDATION_ERROR.message'),
                description: t('payments.missing_order_id'),
                color: 'amber',
            });
            return;
        }
        isSubmitting.value = true;
        try {
            await initiate({
                payable_type: 'order',
                payable_id: orderId.value,
                method: method.value,
            });
            toast.add({
                title: t('app.name'),
                description: t('payments.success_initiated'),
                color: 'green',
            });
            await navigateTo(localePath(`/payments`));
        } catch {
            /* useApi surfaces toast */
        } finally {
            isSubmitting.value = false;
        }
    }
</script>

<template>
    <div class="mx-auto max-w-lg space-y-6">
        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.06em"
            >
                {{ $t('payments.checkout_title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('payments.checkout_hint') }}
            </p>
        </div>

        <UAlert
            v-if="!hasOrderId"
            color="amber"
            variant="soft"
            :title="$t('payments.missing_order_id')"
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
        >
            <template #description>
                <div class="mt-3 flex flex-wrap gap-2">
                    <UButton :to="localePath('/cart')" color="primary" variant="soft">
                        {{ $t('nav.cart') }}
                    </UButton>
                    <UButton :to="localePath('/orders')" color="gray" variant="outline">
                        {{ $t('payments.go_to_orders') }}
                    </UButton>
                </div>
            </template>
        </UAlert>

        <UCard
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
        >
            <div class="space-y-4">
                <UFormGroup :label="$t('payments.method')">
                    <USelect
                        v-model="method"
                        :options="[
                            { value: 'mada', label: $t('payments.method_mada') },
                            { value: 'card', label: $t('payments.method_card') },
                            { value: 'bank_transfer', label: $t('payments.method_bank') },
                        ]"
                        value-attribute="value"
                        option-attribute="label"
                        class="w-full"
                    />
                </UFormGroup>
                <UButton
                    :loading="isSubmitting"
                    :disabled="!hasOrderId"
                    color="primary"
                    @click="submit"
                >
                    {{ $t('payments.submit') }}
                </UButton>
            </div>
        </UCard>
    </div>
</template>
