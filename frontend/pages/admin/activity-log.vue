<script setup lang="ts">
    definePageMeta({
        layout: 'admin',
        middleware: ['auth', 'role'],
        roles: ['admin'],
    });

    const { t } = useI18n();
    const { apiFetch } = useApi();
    const toast = useToast();

    interface ActivityRow {
        id: number;
        action: string;
        subject_type: string;
        subject_id: number;
        created_at?: string;
        actor?: { id: number; name: string } | null;
    }

    const rows = ref<ActivityRow[]>([]);
    const isLoading = ref(false);
    const page = ref(1);
    const perPage = ref(15);
    const total = ref(0);
    const lastPage = ref(1);

    const columns = computed(() => [
        { key: 'id', label: 'ID' },
        { key: 'action', label: t('activityLog.action') },
        { key: 'subject', label: t('activityLog.subject') },
        { key: 'created_at', label: t('activityLog.when') },
    ]);

    function rowSubject(row: ActivityRow): string {
        return `${row.subject_type} #${row.subject_id}`;
    }

    async function fetchLogs() {
        isLoading.value = true;
        try {
            const params = new URLSearchParams({
                page: page.value.toString(),
                per_page: perPage.value.toString(),
            });
            const response = await apiFetch<{
                success: boolean;
                data: { data: ActivityRow[]; meta: { total: number; last_page: number } };
            }>(`/v1/admin/activity-log?${params.toString()}`);

            rows.value = response.data.data;
            total.value = response.data.meta.total;
            lastPage.value = response.data.meta.last_page;
        } catch {
            toast.add({
                title: t('errors.codes.SERVER_ERROR.message'),
                description: t('activityLog.load_error'),
                color: 'error',
            });
        } finally {
            isLoading.value = false;
        }
    }

    watch(page, () => {
        void fetchLogs();
    });

    onMounted(() => {
        void fetchLogs();
    });
</script>

<template>
    <div class="space-y-6">
        <h1
            class="text-2xl font-semibold tracking-tight text-[#171717]"
            style="letter-spacing: -0.06em"
        >
            {{ t('activityLog.admin_title') }}
        </h1>

        <UCard>
            <UTable :rows="rows as any" :columns="columns as any" :loading="isLoading">
                <template #action-data="{ row }">
                    <span class="text-[#171717]">{{ (row as any).action }}</span>
                </template>
                <template #subject-data="{ row }">
                    <span class="text-[#666666]">{{ rowSubject(row as any) }}</span>
                </template>
                <template #created_at-data="{ row }">
                    <span v-if="(row as any).created_at" class="text-[#808080] text-xs">{{
                        new Date((row as any).created_at).toLocaleString('ar-SA')
                    }}</span>
                </template>
            </UTable>

            <div v-if="lastPage > 1" class="mt-4 flex justify-center">
                <UPagination v-model="page" :total="total" :page-count="perPage" />
            </div>
        </UCard>
    </div>
</template>
