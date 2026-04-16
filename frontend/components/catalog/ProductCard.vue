<script setup lang="ts">
    import { productDetailSegment } from '~/composables/useProductCatalogQuery';

    const props = defineProps<{
        product: {
            id: number;
            name: string;
            price: string;
            quantity: number;
            sku?: string | null;
            media?: Array<{
                url?: string | null;
                path?: string | null;
                type?: string | null;
            }> | null;
        };
    }>();

    const localePath = useLocalePath();
    const toast = useToast();
    const cart = useCartStore();
    const { t } = useI18n();
    const segment = computed(() => productDetailSegment(props.product));

    function addToCart() {
        cart.add(
            {
                product_id: props.product.id,
                name: props.product.name,
                price: props.product.price,
                sku: props.product.sku ?? null,
            },
            1
        );
        toast.add({ title: t('catalog.added_to_cart'), color: 'success' });
    }

    const thumbnailUrl = computed(() => {
        const m = props.product.media?.[0];
        const url = (m?.url ?? '').toString().trim();
        return url ? url : null;
    });
</script>

<template>
    <NuxtLink
        :to="localePath(`/products/${segment}`)"
        data-testid="product-card"
        class="block rounded-lg bg-white p-4 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] transition hover:shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_8px_8px_-8px_rgba(0,0,0,0.04)] dark:bg-[#0a0a0a]"
    >
        <div class="mb-3 overflow-hidden rounded-md">
            <img
                v-if="thumbnailUrl"
                :src="thumbnailUrl"
                :alt="product.name"
                class="aspect-[16/9] w-full object-cover"
                loading="lazy"
            />
            <div
                v-else
                class="flex aspect-[16/9] w-full items-center justify-center bg-gradient-to-b from-[#fafafa] to-[#f0f0f0] text-[#a3a3a3] dark:from-[#0a0a0a] dark:to-[#171717] dark:text-[#525252]"
            >
                <UIcon name="i-heroicons-photo" class="h-8 w-8" />
            </div>
        </div>

        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div
                    class="truncate text-base font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.04em"
                >
                    {{ product.name }}
                </div>
                <div class="mt-2 flex flex-wrap gap-3 text-sm text-[#666666]">
                    <span>{{ $t('catalog.price_label') }}: {{ product.price }}</span>
                    <span>{{ $t('catalog.stock_label') }}: {{ product.quantity }}</span>
                </div>
            </div>

            <UButton
                size="xs"
                color="primary"
                variant="soft"
                icon="i-heroicons-plus"
                class="shrink-0"
                @click.prevent="addToCart"
            >
                {{ $t('catalog.add_to_cart') }}
            </UButton>
        </div>
    </NuxtLink>
</template>
