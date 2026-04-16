<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const { apiFetch } = useApi();
    const { hasRole } = useAuth();
    const { t } = useI18n();

    interface EstimateRow {
        id: number;
        title: string;
        status: string;
        grand_total: string;
    }

    const items = ref<EstimateRow[]>([]);
    const isLoading = ref(true);
    const newTitle = ref('');
    const isCreating = ref(false);

    const canMutate = computed(() =>
        hasRole('customer', 'contractor', 'supervising_architect', 'admin')
    );

    function extractList(res: unknown): EstimateRow[] {
        const root = res as { data?: unknown };
        const d = root.data;
        if (Array.isArray(d)) {
            return d as EstimateRow[];
        }
        if (
            d &&
            typeof d === 'object' &&
            'data' in d &&
            Array.isArray((d as { data: EstimateRow[] }).data)
        ) {
            return (d as { data: EstimateRow[] }).data;
        }
        return [];
    }

    async function loadEstimates() {
        isLoading.value = true;
        try {
            const id = route.params.id;
            const res = await apiFetch<unknown>(`/v1/projects/${id}/estimates?per_page=50`);
            items.value = extractList(res);
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(loadEstimates);

    async function createEstimate() {
        if (!newTitle.value.trim()) {
            return;
        }
        isCreating.value = true;
        try {
            const id = route.params.id;
            await apiFetch(`/v1/projects/${id}/estimates`, {
                method: 'POST',
                body: { title: newTitle.value.trim(), markup_percentage: 0 },
            });
            newTitle.value = '';
            await loadEstimates();
        } finally {
            isCreating.value = false;
        }
    }
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <UButton :to="localePath(`/projects/${route.params.id}`)" variant="soft" color="neutral">
            {{ t('projects.estimates_back_project') }}
        </UButton>

        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.06em"
            >
                {{ t('projects.estimates_title') }}
            </h1>
            <p class="mt-2 text-sm text-[#4d4d4d]">
                {{ t('projects.estimates_subtitle') }}
            </p>
        </div>

        <UCard v-if="canMutate" class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <template #header>
                <span class="font-medium text-[#171717] dark:text-white">{{
                    t('projects.estimates_new')
                }}</span>
            </template>
            <div class="flex flex-wrap gap-2">
                <UInput
                    v-model="newTitle"
                    class="min-w-[200px] flex-1"
                    :placeholder="t('projects.estimates_new')"
                />
                <UButton :loading="isCreating" class="font-medium" @click="createEstimate">
                    {{ t('projects.estimates_new') }}
                </UButton>
            </div>
        </UCard>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ t('shell.loading') }}
        </div>

        <UCard v-else class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <template #header>
                <span class="font-medium text-[#171717] dark:text-white">{{
                    t('projects.open_estimates')
                }}</span>
            </template>
            <p v-if="items.length === 0" class="text-sm text-[#666666]">
                {{ t('projects.estimates_empty') }}
            </p>
            <ul v-else class="space-y-2 text-sm text-[#4d4d4d]">
                <li
                    v-for="e in items"
                    :key="e.id"
                    class="flex flex-wrap items-center justify-between gap-2 rounded-md bg-[#fafafa] px-3 py-2 dark:bg-neutral-900"
                >
                    <span class="font-medium text-[#171717] dark:text-white">{{ e.title }}</span>
                    <div class="flex flex-wrap items-center gap-2">
                        <UBadge color="neutral" variant="soft">{{ e.status }}</UBadge>
                        <span class="text-xs text-[#666666]">{{ e.grand_total }}</span>
                        <UButton
                            size="xs"
                            variant="soft"
                            :to="localePath(`/projects/${route.params.id}/estimates/${e.id}`)"
                        >
                            {{ t('projects.estimates_open') }}
                        </UButton>
                    </div>
                </li>
            </ul>
        </UCard>
    </div>
</template>
