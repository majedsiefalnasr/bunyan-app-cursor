<script setup lang="ts">
    import ProductCard from '~/components/catalog/ProductCard.vue';

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
</script>

<template>
    <ProductCard :product="props.product" v-bind="$attrs" />
</template>
