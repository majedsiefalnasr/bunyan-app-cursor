<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const config = useRuntimeConfig();
    const authStore = useAuthStore();
    const { apiFetch } = useApi();
    const { hasRole } = useAuth();
    const { t } = useI18n();

    interface ItemRow {
        id: number;
        category: string;
        quantity: string;
        unit: string;
        unit_price: string;
        total_price: string;
        description_ar: string | null;
    }

    interface EstimateDetail {
        id: number;
        title: string;
        status: string;
        grand_total: string;
        markup_percentage: string;
        items?: ItemRow[];
    }

    const estimate = ref<EstimateDetail | null>(null);
    const isLoading = ref(true);
    const isBusy = ref(false);

    const canMutate = computed(() =>
        hasRole('customer', 'contractor', 'supervising_architect', 'admin')
    );

    async function loadEstimate() {
        isLoading.value = true;
        try {
            const eid = route.params.estimateId;
            const res = await apiFetch<{ data: EstimateDetail }>(`/v1/estimates/${eid}`);
            estimate.value = res.data ?? null;
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(loadEstimate);

    async function recalculate() {
        if (!estimate.value) {
            return;
        }
        isBusy.value = true;
        try {
            const res = await apiFetch<{ data: EstimateDetail }>(
                `/v1/estimates/${estimate.value.id}/calculate`,
                { method: 'POST' }
            );
            estimate.value = res.data ?? estimate.value;
        } finally {
            isBusy.value = false;
        }
    }

    function exportCsvUrl(): string {
        const id = route.params.estimateId;
        const root = (config.public.apiBaseUrl || '').replace(/\/$/, '');
        const path = `/api/v1/estimates/${id}/export`;
        return root ? `${root}${path}` : path;
    }

    async function exportCsv() {
        const token = authStore.token;
        if (!token) {
            return;
        }
        isBusy.value = true;
        try {
            const res = await fetch(exportCsvUrl(), {
                headers: { Authorization: `Bearer ${token}`, Accept: 'text/csv' },
            });
            const blob = await res.blob();
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `estimate-${route.params.estimateId}-boq.csv`;
            a.click();
            URL.revokeObjectURL(url);
        } finally {
            isBusy.value = false;
        }
    }
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <UButton
            :to="localePath(`/projects/${route.params.id}/estimates`)"
            variant="soft"
            color="gray"
        >
            {{ t('projects.open_estimates') }}
        </UButton>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ t('shell.loading') }}
        </div>

        <template v-else-if="estimate">
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.06em"
                >
                    {{ estimate.title }}
                </h1>
                <p class="mt-2 text-sm text-[#4d4d4d]">
                    {{ t('projects.estimates_status') }}: {{ estimate.status }} —
                    {{ t('projects.estimates_grand_total') }}: {{ estimate.grand_total }} ({{
                        t('projects.estimates_markup')
                    }}: {{ estimate.markup_percentage }})
                </p>
            </div>

            <div v-if="canMutate" class="flex flex-wrap gap-2">
                <UButton :loading="isBusy" class="font-medium" @click="recalculate">
                    {{ t('projects.estimates_calculate') }}
                </UButton>
                <UButton variant="soft" color="gray" :loading="isBusy" @click="exportCsv">
                    {{ t('projects.estimates_export') }}
                </UButton>
            </div>

            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <template #header>
                    <span class="font-medium text-[#171717] dark:text-white">{{
                        t('projects.estimates_items')
                    }}</span>
                </template>
                <p v-if="!estimate.items?.length" class="text-sm text-[#666666]">
                    {{ t('projects.estimates_empty') }}
                </p>
                <ul v-else class="space-y-2 text-sm text-[#4d4d4d]">
                    <li
                        v-for="it in estimate.items"
                        :key="it.id"
                        class="rounded-md bg-[#fafafa] px-3 py-2 dark:bg-neutral-900"
                    >
                        <span class="font-medium text-[#171717] dark:text-white">{{
                            it.description_ar || '—'
                        }}</span>
                        <span class="mt-1 block text-xs text-[#666666]">
                            {{ it.category }} · {{ it.quantity }} {{ it.unit }} ×
                            {{ it.unit_price }} =
                            {{ it.total_price }}
                        </span>
                    </li>
                </ul>
            </UCard>
        </template>
    </div>
</template>
