<script setup lang="ts">
    import { extractProductListPayload } from '~/composables/useProductCatalogQuery';
    import type { CategoryNode } from '~/types/category';

    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    interface ProductRow {
        id: number;
        name: string;
        price: string;
        quantity: number;
        sku?: string | null;
    }

    const route = useRoute();
    const localePath = useLocalePath();
    const { apiFetch } = useApi();

    const category = ref<CategoryNode | null>(null);
    const products = ref<ProductRow[]>([]);
    const isLoading = ref(true);

    async function load() {
        isLoading.value = true;
        const slug = route.params.slug as string;
        try {
            const cRes = await apiFetch<{ data: CategoryNode }>(`/v1/categories/${slug}`);
            category.value = cRes.data ?? null;
            const params = new URLSearchParams();
            params.set('per_page', '24');
            if (category.value) {
                params.set('category_id', String(category.value.id));
            }
            const pRes = await apiFetch<{ data: unknown }>(`/v1/products?${params.toString()}`);
            const { items } = extractProductListPayload(pRes);
            products.value = items as ProductRow[];
        } catch {
            category.value = null;
            products.value = [];
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
</script>

<template>
    <div class="mx-auto max-w-5xl space-y-6">
        <UButton :to="localePath('/categories')" color="neutral" variant="ghost" class="px-0">
            {{ $t('categories.back_grid') }}
        </UButton>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <template v-else-if="category">
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.06em"
                >
                    {{ category.name_ar }}
                </h1>
                <p v-if="category.name_en" class="mt-1 text-sm text-[#666666]">
                    {{ category.name_en }}
                </p>
            </div>

            <p v-if="products.length === 0" class="text-sm text-[#666666]">
                {{ $t('catalog.empty') }}
            </p>
            <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <CatalogProductCard v-for="p in products" :key="p.id" :product="p" />
            </div>
        </template>

        <p v-else class="text-sm text-[#666666]">
            {{ $t('categories.not_found') }}
        </p>
    </div>
</template>
