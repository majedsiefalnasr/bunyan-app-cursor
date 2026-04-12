<script setup lang="ts">
    definePageMeta({
        layout: 'default',
    });

    const localePath = useLocalePath();
    const { apiFetch } = useApi();

    interface SupplierRow {
        id: number;
        company_name_ar: string;
        company_name_en: string | null;
        city: string | null;
        rating_avg: string;
        verification_status: string;
    }

    const suppliers = ref<SupplierRow[]>([]);
    const isLoading = ref(true);

    onMounted(async () => {
        try {
            const res = await apiFetch<{ data: SupplierRow[] }>('/v1/suppliers');
            suppliers.value = res.data ?? [];
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-6">
        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.06em"
            >
                {{ $t('suppliers.directory_title') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('suppliers.directory_subtitle') }}
            </p>
        </div>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <p v-else-if="suppliers.length === 0" class="text-sm text-[#666666]">
            {{ $t('suppliers.empty') }}
        </p>

        <div v-else class="grid gap-4 sm:grid-cols-2">
            <UCard
                v-for="s in suppliers"
                :key="s.id"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
            >
                <div class="space-y-2">
                    <h2 class="text-lg font-semibold text-[#171717] dark:text-white">
                        {{ s.company_name_ar }}
                    </h2>
                    <p v-if="s.city" class="text-sm text-[#4d4d4d]">{{ s.city }}</p>
                    <p class="text-xs text-[#666666]">
                        {{ $t('suppliers.rating') }}: {{ s.rating_avg }}
                    </p>
                    <UButton :to="localePath(`/suppliers/${s.id}`)" variant="soft" color="neutral">
                        {{ $t('suppliers.view_products') }}
                    </UButton>
                </div>
            </UCard>
        </div>
    </div>
</template>
