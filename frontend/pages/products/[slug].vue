<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    interface VariantRow {
        id: number;
        name: string;
        sku: string;
        stock_quantity: number;
        price_modifier: string;
    }

    interface PriceTierRow {
        id: number;
        product_id: number;
        product_variant_id: number | null;
        min_quantity: number;
        max_quantity: number | null;
        unit_price: string;
    }

    interface ProductDetail {
        id: number;
        name: string;
        description: string | null;
        price: string;
        quantity: number;
        variants?: VariantRow[];
        price_tiers?: PriceTierRow[];
        media?: Array<{ url?: string | null; path?: string | null; type?: string | null }> | null;
    }

    const route = useRoute();
    const localePath = useLocalePath();
    const { apiFetch } = useApi();
    const { formatSar } = useSarPriceFormat();
    const cart = useCartStore();
    const toast = useToast();
    const { t } = useI18n();

    const product = ref<ProductDetail | null>(null);
    const isLoading = ref(true);
    const qty = ref(1);

    async function load() {
        isLoading.value = true;
        try {
            const slug = route.params.slug as string;
            const res = await apiFetch<{ data: ProductDetail }>(`/v1/products/${slug}`);
            product.value = res.data ?? null;
        } catch {
            product.value = null;
        } finally {
            isLoading.value = false;
        }
    }

    watch(
        () => route.params.slug,
        () => {
            void load();
        },
        { immediate: true }
    );

    function addToCart() {
        if (!product.value) return;
        cart.add(
            {
                product_id: product.value.id,
                name: product.value.name,
                price: product.value.price,
            },
            qty.value
        );
        toast.add({ title: t('catalog.added_to_cart'), color: 'green' });
    }

    const heroUrl = computed(() => {
        const url = (product.value?.media?.[0]?.url ?? '').toString().trim();
        return url ? url : null;
    });
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <UButton :to="localePath('/products')" color="gray" variant="ghost" class="px-0">
            {{ $t('catalog.back_list') }}
        </UButton>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <div v-else-if="!product" class="text-sm text-[#666666]">
            {{ $t('catalog.empty') }}
        </div>

        <div v-else class="space-y-4">
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.06em"
                >
                    {{ product.name }}
                </h1>
                <p v-if="product.description" class="mt-2 text-sm leading-relaxed text-[#4d4d4d]">
                    {{ product.description }}
                </p>
            </div>

            <div
                class="overflow-hidden rounded-lg bg-white shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] dark:bg-[#0a0a0a]"
            >
                <img
                    v-if="heroUrl"
                    :src="heroUrl"
                    :alt="product.name"
                    class="aspect-[16/9] w-full object-cover"
                    loading="lazy"
                />
                <div
                    v-else
                    class="flex aspect-[16/9] items-center justify-center bg-gradient-to-b from-[#fafafa] to-[#f0f0f0] text-[#a3a3a3] dark:from-[#0a0a0a] dark:to-[#171717] dark:text-[#525252]"
                >
                    <UIcon name="i-heroicons-photo" class="h-10 w-10" />
                </div>
            </div>

            <div
                class="rounded-lg bg-white p-4 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] dark:bg-[#0a0a0a]"
            >
                <p class="text-sm text-[#666666]">{{ $t('catalog.detail_title') }}</p>
                <dl class="mt-3 grid gap-2 text-sm text-[#171717] dark:text-white">
                    <div class="flex justify-between gap-4">
                        <dt>{{ $t('catalog.price_label') }}</dt>
                        <dd class="font-medium">{{ formatSar(product.price) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt>{{ $t('catalog.stock_label') }}</dt>
                        <dd class="font-medium">{{ product.quantity }}</dd>
                    </div>
                </dl>

                <div
                    class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-[#666666]">{{ $t('cart.quantity') }}</span>
                        <UInput v-model="qty" type="number" min="1" class="w-24" />
                    </div>
                    <UButton
                        color="primary"
                        variant="solid"
                        icon="i-heroicons-plus"
                        @click="addToCart"
                    >
                        {{ $t('catalog.add_to_cart') }}
                    </UButton>
                </div>
            </div>

            <div
                v-if="product.price_tiers && product.price_tiers.length"
                class="rounded-lg bg-white p-4 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] dark:bg-[#0a0a0a]"
            >
                <h2
                    class="text-base font-semibold text-[#171717] dark:text-white"
                    style="letter-spacing: -0.04em"
                >
                    {{ $t('catalog.tiers_title') }}
                </h2>
                <table class="mt-3 w-full text-start text-sm text-[#4d4d4d]">
                    <thead>
                        <tr class="border-b border-[#ebebeb] dark:border-[#262626]">
                            <th class="py-2 font-medium text-[#171717] dark:text-white">
                                {{ $t('catalog.tier_qty_range') }}
                            </th>
                            <th class="py-2 font-medium text-[#171717] dark:text-white">
                                {{ $t('catalog.tier_unit_price') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="tier in product.price_tiers"
                            :key="tier.id"
                            class="border-b border-[#ebebeb] last:border-0 dark:border-[#262626]"
                        >
                            <td class="py-2">
                                {{ tier.min_quantity }}
                                —
                                {{ tier.max_quantity ?? $t('catalog.tier_open') }}
                            </td>
                            <td class="py-2 font-medium text-[#171717] dark:text-white">
                                {{ formatSar(tier.unit_price) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="product.variants && product.variants.length"
                class="rounded-lg bg-white p-4 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] dark:bg-[#0a0a0a]"
            >
                <h2
                    class="text-base font-semibold text-[#171717] dark:text-white"
                    style="letter-spacing: -0.04em"
                >
                    {{ $t('catalog.variants_title') }}
                </h2>
                <ul class="mt-3 space-y-2 text-sm text-[#4d4d4d]">
                    <li
                        v-for="v in product.variants"
                        :key="v.id"
                        class="flex flex-col border-t border-[#ebebeb] pt-2 first:border-t-0 first:pt-0 dark:border-[#262626]"
                    >
                        <span class="font-medium text-[#171717] dark:text-white">{{ v.name }}</span>
                        <span class="text-xs text-[#666666]"
                            >SKU {{ v.sku }} · +{{ v.price_modifier }} ·
                            {{ v.stock_quantity }}</span
                        >
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
