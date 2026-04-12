<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    interface VariantRow {
        id: number;
        name: string;
        sku: string;
        stock_quantity: number;
        price_modifier: string;
    }

    interface ProductDetail {
        id: number;
        name: string;
        description: string | null;
        price: string;
        quantity: number;
        variants?: VariantRow[];
    }

    const route = useRoute();
    const localePath = useLocalePath();
    const { apiFetch } = useApi();

    const product = ref<ProductDetail | null>(null);
    const isLoading = ref(true);

    async function load() {
        isLoading.value = true;
        try {
            const id = route.params.id;
            const res = await apiFetch<{ data: ProductDetail }>(`/v1/products/${id}`);
            product.value = res.data ?? null;
        } catch {
            product.value = null;
        } finally {
            isLoading.value = false;
        }
    }

    watch(
        () => route.params.id,
        () => {
            void load();
        },
        { immediate: true }
    );
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
                class="rounded-lg bg-white p-4 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] dark:bg-[#0a0a0a]"
            >
                <p class="text-sm text-[#666666]">{{ $t('catalog.detail_title') }}</p>
                <dl class="mt-3 grid gap-2 text-sm text-[#171717] dark:text-white">
                    <div class="flex justify-between gap-4">
                        <dt>{{ $t('catalog.price_label') }}</dt>
                        <dd class="font-medium">{{ product.price }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt>{{ $t('catalog.stock_label') }}</dt>
                        <dd class="font-medium">{{ product.quantity }}</dd>
                    </div>
                </dl>
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
