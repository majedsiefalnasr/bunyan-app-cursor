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
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
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

        <UPageGrid v-else class="gap-4 sm:gap-6 lg:grid-cols-3">
            <UPageCard
                v-for="s in suppliers"
                :key="s.id"
                variant="subtle"
                :title="s.company_name_ar"
                :ui="{
                    container: 'gap-y-2',
                    title: 'font-medium text-[#171717] dark:text-white',
                }"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]"
            >
                <div class="space-y-2">
                    <p v-if="s.city" class="text-sm text-[#4d4d4d]">{{ s.city }}</p>
                    <p class="text-xs text-[#666666]">
                        {{ $t('suppliers.rating') }}: {{ s.rating_avg }}
                    </p>
                    <UButton
                        :to="localePath(`/suppliers/${s.id}`)"
                        variant="soft"
                        color="neutral"
                        size="xs"
                    >
                        {{ $t('suppliers.view_products') }}
                    </UButton>
                </div>
            </UPageCard>
        </UPageGrid>
    </div>
</template>
