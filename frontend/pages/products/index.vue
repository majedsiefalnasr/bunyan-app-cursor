<script setup lang="ts">
    import { watchDebounced } from '@vueuse/core';
    import type { CategoryNode } from '~/types/category';
    import {
        buildProductListQueryString,
        defaultProductCatalogFilters,
        extractProductListPayload,
        type ProductCatalogFilters,
        type ProductListMeta,
    } from '~/composables/useProductCatalogQuery';

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
        media?: Array<{ url?: string | null; path?: string | null; type?: string | null }> | null;
    }

    const { apiFetch } = useApi();

    const filters = ref<ProductCatalogFilters>(defaultProductCatalogFilters());
    const products = ref<ProductRow[]>([]);
    const meta = ref<ProductListMeta | null>(null);
    const isLoading = ref(true);
    const loadError = ref<string | null>(null);
    const categoryTree = ref<CategoryNode[]>([]);
    const isCategoriesLoading = ref(true);

    const categoryOptions = computed(() => {
        const out: { id: number; name_ar: string }[] = [];
        function walk(nodes: CategoryNode[]) {
            for (const n of nodes) {
                out.push({ id: n.id, name_ar: n.name_ar });
                if (n.children?.length) {
                    walk(n.children);
                }
            }
        }
        walk(categoryTree.value);
        return out;
    });

    async function loadCategories() {
        isCategoriesLoading.value = true;
        try {
            const res = await apiFetch<{ data: CategoryNode[] }>('/v1/categories');
            categoryTree.value = res.data ?? [];
        } catch {
            categoryTree.value = [];
        } finally {
            isCategoriesLoading.value = false;
        }
    }

    async function loadProducts() {
        isLoading.value = true;
        loadError.value = null;
        try {
            const qs = buildProductListQueryString(filters.value);
            const res = await apiFetch<{ data: unknown }>(`/v1/products?${qs}`);
            const { items, meta: m } = extractProductListPayload(res);
            products.value = items as ProductRow[];
            meta.value = m;
        } catch (e: unknown) {
            products.value = [];
            meta.value = null;
            loadError.value = e instanceof Error ? e.message : String(e);
        } finally {
            isLoading.value = false;
        }
    }

    function onFilterApply() {
        filters.value.page = 1;
        void loadProducts();
    }

    watchDebounced(
        () => filters.value.search,
        () => {
            filters.value.page = 1;
            void loadProducts();
        },
        { debounce: 350 }
    );

    function onPageChange(p: number) {
        filters.value.page = p;
        void loadProducts();
    }

    onMounted(async () => {
        await loadCategories();
        await loadProducts();
    });
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                {{ $t('catalog.list_title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('catalog.list_subtitle') }}
            </p>
        </div>

        <div class="flex flex-col gap-6 lg:flex-row">
            <aside class="lg:w-72 lg:shrink-0">
                <ProductFilterSidebar
                    v-model="filters"
                    :category-options="categoryOptions"
                    :is-categories-loading="isCategoriesLoading"
                    @apply="onFilterApply"
                />
            </aside>

            <div class="min-w-0 flex-1 space-y-4">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    <UInput
                        v-model="filters.search"
                        class="flex-1"
                        :placeholder="$t('catalog.search_placeholder')"
                        @keyup.enter="onFilterApply"
                    />
                    <UButton
                        color="primary"
                        variant="solid"
                        class="shrink-0"
                        @click="onFilterApply"
                    >
                        {{ $t('catalog.search_action') }}
                    </UButton>
                </div>

                <div v-if="isLoading" class="space-y-4">
                    <div class="grid gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <UCard
                            v-for="i in 9"
                            :key="i"
                            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]"
                        >
                            <div class="space-y-3">
                                <USkeleton class="h-40 w-full rounded-lg" />
                                <div class="space-y-2">
                                    <USkeleton class="h-4 w-3/4" />
                                    <USkeleton class="h-4 w-1/2" />
                                </div>
                                <USkeleton class="h-8 w-28 rounded-md" />
                            </div>
                        </UCard>
                    </div>
                </div>

                <UAlert
                    v-else-if="loadError"
                    color="error"
                    variant="soft"
                    :title="$t('shell.error')"
                    :description="loadError"
                />

                <p v-else-if="products.length === 0" class="text-sm text-[#666666]">
                    {{ $t('catalog.empty') }}
                </p>

                <div v-else class="grid gap-4 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <CatalogProductCard v-for="p in products" :key="p.id" :product="p" />
                </div>

                <div
                    v-if="meta && meta.last_page > 1"
                    class="flex justify-center pt-2"
                    data-testid="pagination-wrap"
                >
                    <UPagination
                        :model-value="filters.page"
                        :total="meta.total"
                        :page-count="meta.per_page"
                        @update:model-value="onPageChange"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
