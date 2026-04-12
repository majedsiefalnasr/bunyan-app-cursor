<script setup lang="ts">
    import { productDetailSegment } from '~/composables/useProductCatalogQuery';

    const props = defineProps<{
        product: {
            id: number;
            name: string;
            price: string;
            quantity: number;
            sku?: string | null;
        };
    }>();

    const localePath = useLocalePath();
    const segment = computed(() => productDetailSegment(props.product));
</script>

<template>
    <NuxtLink
        :to="localePath(`/products/${segment}`)"
        data-testid="product-card"
        class="block rounded-lg bg-white p-4 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] transition hover:shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_8px_8px_-8px_rgba(0,0,0,0.04)] dark:bg-[#0a0a0a]"
    >
        <div
            class="text-base font-semibold tracking-tight text-[#171717] dark:text-white"
            style="letter-spacing: -0.04em"
        >
            {{ product.name }}
        </div>
        <div class="mt-2 flex flex-wrap gap-3 text-sm text-[#666666]">
            <span>{{ $t('catalog.price_label') }}: {{ product.price }}</span>
            <span>{{ $t('catalog.stock_label') }}: {{ product.quantity }}</span>
        </div>
    </NuxtLink>
</template>
