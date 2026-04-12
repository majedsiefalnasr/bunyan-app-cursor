<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    interface ProductRow {
        id: number;
        name: string;
        price: string;
        quantity: number;
        category: string | null;
    }

    const localePath = useLocalePath();
    const { apiFetch } = useApi();

    const products = ref<ProductRow[]>([]);
    const isLoading = ref(true);
    const q = ref('');

    async function load() {
        isLoading.value = true;
        try {
            const params = new URLSearchParams();
            params.set('per_page', '30');
            if (q.value.trim()) {
                params.set('search', q.value.trim());
            }
            const res = await apiFetch<{ data: ProductRow[] }>(`/v1/products?${params.toString()}`);
            products.value = Array.isArray(res.data) ? res.data : [];
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(() => {
        void load();
    });
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.06em"
            >
                {{ $t('catalog.list_title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('catalog.list_subtitle') }}
            </p>
        </div>

        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <UInput
                v-model="q"
                class="flex-1"
                :placeholder="$t('catalog.search_placeholder')"
                @keyup.enter="load"
            />
            <UButton color="primary" variant="solid" class="shrink-0" @click="load">
                {{ $t('catalog.search_action') }}
            </UButton>
        </div>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <p v-else-if="products.length === 0" class="text-sm text-[#666666]">
            {{ $t('catalog.empty') }}
        </p>

        <div v-else class="grid gap-4 sm:grid-cols-2">
            <NuxtLink
                v-for="p in products"
                :key="p.id"
                :to="localePath(`/products/${p.id}`)"
                class="block rounded-lg bg-white p-4 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] transition hover:shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_8px_8px_-8px_rgba(0,0,0,0.04)] dark:bg-[#0a0a0a]"
            >
                <div
                    class="text-base font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.04em"
                >
                    {{ p.name }}
                </div>
                <div class="mt-2 flex flex-wrap gap-3 text-sm text-[#666666]">
                    <span>{{ $t('catalog.price_label') }}: {{ p.price }}</span>
                    <span>{{ $t('catalog.stock_label') }}: {{ p.quantity }}</span>
                </div>
            </NuxtLink>
        </div>
    </div>
</template>
