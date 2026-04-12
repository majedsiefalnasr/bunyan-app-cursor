<script setup lang="ts">
    definePageMeta({
        middleware: ['auth', 'role'],
        roles: ['admin'],
    });

    const { t } = useI18n();
    const { apiFetch } = useApi();
    const toast = useToast();

    interface WorkflowRow {
        id: number;
        name: string;
        type?: string | null;
        is_global: boolean;
        is_active: boolean;
    }

    const rows = ref<WorkflowRow[]>([]);
    const isLoading = ref(false);
    const page = ref(1);
    const perPage = ref(15);
    const total = ref(0);
    const lastPage = ref(1);

    const columns = computed(() => [
        { key: 'id', label: t('workflow.col_id') },
        { key: 'name', label: t('workflow.col_name') },
        { key: 'type', label: t('workflow.col_type') },
        { key: 'is_global', label: t('workflow.col_global') },
        { key: 'is_active', label: t('workflow.col_active') },
    ]);

    async function fetchWorkflows() {
        isLoading.value = true;
        try {
            const params = new URLSearchParams({
                page: page.value.toString(),
                per_page: perPage.value.toString(),
            });
            const response = await apiFetch<{
                success: boolean;
                data: { data: WorkflowRow[]; meta: { total: number; last_page: number } };
            }>(`/v1/workflows?${params.toString()}`);

            rows.value = response.data.data;
            total.value = response.data.meta.total;
            lastPage.value = response.data.meta.last_page;
        } catch {
            toast.add({
                title: t('errors.codes.SERVER_ERROR.message'),
                description: t('workflow.load_error'),
                color: 'red',
            });
        } finally {
            isLoading.value = false;
        }
    }

    watch(page, () => {
        void fetchWorkflows();
    });

    onMounted(() => {
        void fetchWorkflows();
    });
</script>

<template>
    <div class="space-y-6">
        <h1 class="text-2xl font-semibold tracking-tight text-white">
            {{ t('workflow.admin_title') }}
        </h1>

        <div class="rounded-lg bg-slate-800 p-4 shadow-[0px_0px_0px_1px_rgba(255,255,255,0.08)]">
            <UTable :rows="rows" :columns="columns" :loading="isLoading">
                <template #is_global-data="{ row }">
                    <span class="text-slate-300">{{ row.is_global ? '✓' : '—' }}</span>
                </template>
                <template #is_active-data="{ row }">
                    <span class="text-slate-300">{{ row.is_active ? '✓' : '—' }}</span>
                </template>
            </UTable>

            <div v-if="lastPage > 1" class="mt-4 flex justify-center">
                <UPagination v-model="page" :total="total" :page-count="perPage" />
            </div>
        </div>
    </div>
</template>
