<script setup lang="ts">
    definePageMeta({
        layout: 'admin',
        middleware: ['auth', 'role'],
        roles: ['admin'],
    });

    const { t } = useI18n();
    const { apiFetch } = useApi();
    const auth = useAuthStore();
    const config = useRuntimeConfig();
    const toast = useToast();

    interface ReportTypeMeta {
        type: string;
        label_key: string;
        exports: string[];
    }

    const types = ref<ReportTypeMeta[]>([]);
    const selectedType = ref<string>('sales_summary');
    const dateFrom = ref<string>('');
    const dateTo = ref<string>('');
    const isLoadingTypes = ref(false);
    const isLoadingReport = ref(false);
    const isExporting = ref(false);
    const summary = ref<Record<string, unknown> | null>(null);
    const rows = ref<Record<string, unknown>[]>([]);
    const meta = ref<{ truncated?: boolean; stub?: boolean } | null>(null);

    const columns = computed(() => {
        const first = rows.value[0];
        if (!first) {
            return [];
        }
        return Object.keys(first).map((key) => ({
            key,
            label: key,
        }));
    });

    async function loadTypes() {
        isLoadingTypes.value = true;
        try {
            const res = await apiFetch<{ success: boolean; data: { types: ReportTypeMeta[] } }>(
                '/v1/admin/analytics/reports/types'
            );
            types.value = res.data.types;
            if (types.value.length > 0) {
                selectedType.value = types.value[0].type;
            }
        } catch {
            toast.add({ title: t('analyticsReports.load_types_error'), color: 'red' });
        } finally {
            isLoadingTypes.value = false;
        }
    }

    function typeLabel(type: string): string {
        const key = `analyticsReports.types.${type}` as const;
        return t(key);
    }

    function buildQuery(): string {
        const p = new URLSearchParams();
        if (dateFrom.value) {
            p.set('date_from', dateFrom.value);
        }
        if (dateTo.value) {
            p.set('date_to', dateTo.value);
        }
        const s = p.toString();

        return s ? `?${s}` : '';
    }

    async function runReport() {
        isLoadingReport.value = true;
        summary.value = null;
        rows.value = [];
        meta.value = null;
        try {
            const res = await apiFetch<{
                success: boolean;
                data: {
                    meta: { truncated?: boolean; stub?: boolean };
                    summary: Record<string, unknown>;
                    rows: Record<string, unknown>[];
                };
            }>(`/v1/admin/analytics/reports/${selectedType.value}${buildQuery()}`);
            meta.value = res.data.meta;
            summary.value = res.data.summary;
            rows.value = res.data.rows;
        } catch {
            toast.add({ title: t('analyticsReports.load_report_error'), color: 'red' });
        } finally {
            isLoadingReport.value = false;
        }
    }

    async function exportReport(format: 'pdf' | 'xlsx') {
        const token = auth.token;
        if (!token) {
            return;
        }
        isExporting.value = true;
        try {
            const params = new URLSearchParams({ format });
            if (dateFrom.value) {
                params.set('date_from', dateFrom.value);
            }
            if (dateTo.value) {
                params.set('date_to', dateTo.value);
            }
            const path = `/v1/admin/analytics/reports/${selectedType.value}/export?${params.toString()}`;
            const baseRaw = (config.public.apiBaseUrl as string) || '';
            const url =
                baseRaw === ''
                    ? path
                    : `${baseRaw.replace(/\/$/, '')}${path.startsWith('/') ? path : `/${path}`}`;
            const res = await fetch(url, {
                headers: {
                    Authorization: `Bearer ${token}`,
                    Accept: '*/*',
                },
            });
            if (!res.ok) {
                toast.add({ title: t('analyticsReports.export_error'), color: 'red' });

                return;
            }
            const blob = await res.blob();
            const ext = format === 'pdf' ? 'pdf' : 'xlsx';
            const href = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = href;
            a.download = `${selectedType.value}.${ext}`;
            a.click();
            URL.revokeObjectURL(href);
        } catch {
            toast.add({ title: t('analyticsReports.export_error'), color: 'red' });
        } finally {
            isExporting.value = false;
        }
    }

    onMounted(() => {
        void loadTypes();
    });
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717]"
                style="letter-spacing: -0.06em"
            >
                {{ t('analyticsReports.title') }}
            </h1>
            <p class="mt-1 text-sm text-[#4d4d4d]">{{ t('analyticsReports.subtitle') }}</p>
        </div>

        <UCard class="space-y-4">
            <UFormGroup :label="t('analyticsReports.type_label')">
                <USelect
                    v-model="selectedType"
                    :options="types.map((x) => ({ value: x.type, label: typeLabel(x.type) }))"
                    value-attribute="value"
                    option-attribute="label"
                    :loading="isLoadingTypes"
                />
            </UFormGroup>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <UFormGroup :label="t('analyticsReports.date_from')">
                    <UInput v-model="dateFrom" type="date" />
                </UFormGroup>
                <UFormGroup :label="t('analyticsReports.date_to')">
                    <UInput v-model="dateTo" type="date" />
                </UFormGroup>
            </div>
            <div class="flex flex-wrap gap-2">
                <UButton :loading="isLoadingReport" @click="runReport">{{
                    t('analyticsReports.run')
                }}</UButton>
                <UButton
                    variant="outline"
                    color="gray"
                    :loading="isExporting"
                    @click="exportReport('pdf')"
                >
                    {{ t('analyticsReports.export_pdf') }}
                </UButton>
                <UButton
                    variant="outline"
                    color="gray"
                    :loading="isExporting"
                    @click="exportReport('xlsx')"
                >
                    {{ t('analyticsReports.export_xlsx') }}
                </UButton>
            </div>
            <p v-if="meta?.truncated" class="text-amber-300 text-sm">
                Results truncated (row cap).
            </p>
            <p v-if="meta?.stub" class="text-amber-300 text-sm">
                Financial figures may be partial (stub mode).
            </p>
        </UCard>

        <UCard v-if="summary">
            <h2 class="text-lg font-medium text-white mb-2">
                {{ t('analyticsReports.summary_title') }}
            </h2>
            <pre class="text-sm text-slate-200 overflow-x-auto">{{
                JSON.stringify(summary, null, 2)
            }}</pre>
        </UCard>

        <UCard>
            <UTable :rows="rows" :columns="columns" :loading="isLoadingReport" />
            <p v-if="!isLoadingReport && rows.length === 0" class="text-slate-400 mt-2 text-sm">
                {{ t('analyticsReports.empty_rows') }}
            </p>
        </UCard>
    </div>
</template>
