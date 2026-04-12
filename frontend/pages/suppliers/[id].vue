<script setup lang="ts">
    definePageMeta({
        layout: 'default',
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const { apiFetch } = useApi();

    interface SupplierDetail {
        id: number;
        company_name_ar: string;
        company_name_en: string | null;
        city: string | null;
        address: string | null;
        phone: string | null;
        verification_status: string;
        rating_avg: string;
    }

    interface ProductRow {
        id: number;
        name: string;
        price: string;
    }

    const supplier = ref<SupplierDetail | null>(null);
    const products = ref<ProductRow[]>([]);
    const isLoading = ref(true);

    onMounted(async () => {
        const id = route.params.id as string;
        try {
            const sRes = await apiFetch<{ data: SupplierDetail }>(`/v1/suppliers/${id}`);
            supplier.value = sRes.data ?? null;
            const pRes = await apiFetch<{ data: ProductRow[] }>(`/v1/suppliers/${id}/products`);
            products.value = pRes.data ?? [];
        } catch {
            supplier.value = null;
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <UButton :to="localePath('/suppliers')" variant="ghost" color="neutral" class="mb-2">
            ← {{ $t('nav.suppliers') }}
        </UButton>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <div v-else-if="!supplier" class="text-sm text-[#666666]">
            {{ $t('errors.pages.404.description') }}
        </div>

        <template v-else>
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                    {{ supplier.company_name_ar }}
                </h1>
                <p v-if="supplier.city" class="mt-1 text-sm text-[#4d4d4d]">{{ supplier.city }}</p>
            </div>

            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <template #header>
                    <span class="font-medium">{{ $t('suppliers.products_title') }}</span>
                </template>
                <ul
                    v-if="products.length"
                    class="divide-y divide-[#ebebeb] dark:divide-neutral-800"
                >
                    <li
                        v-for="p in products"
                        :key="p.id"
                        class="flex items-center justify-between py-3 text-sm rtl:flex-row-reverse"
                    >
                        <span>{{ p.name }}</span>
                        <span class="text-[#666666]">{{ p.price }}</span>
                    </li>
                </ul>
                <p v-else class="text-sm text-[#666666]">{{ $t('suppliers.empty') }}</p>
            </UCard>
        </template>
    </div>
</template>
