<script setup lang="ts">
    import type {
        ActivityLogRow,
        DashboardKpis,
        DashboardOverview,
        PaginatedActivity,
    } from '~/composables/useDashboard';

    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const localePath = useLocalePath();
    const { role } = useAuth();
    const { t } = useI18n();
    const { fetchOverview, fetchRecentActivity } = useDashboard();

    const overview = ref<DashboardOverview | null>(null);
    const activity = ref<PaginatedActivity | null>(null);
    const loading = ref(true);

    const kpiLabelKeys: Record<keyof DashboardKpis, string> = {
        users: 'dashboard.kpi_users',
        projects: 'dashboard.kpi_projects',
        orders: 'dashboard.kpi_orders',
        revenue_sar: 'dashboard.kpi_revenue_sar',
        tasks_assigned: 'dashboard.kpi_tasks_assigned',
        reports: 'dashboard.kpi_reports',
    };

    const kpiEntries = computed(() => {
        const k = overview.value?.kpis as DashboardKpis | undefined;
        if (!k) {
            return [];
        }
        const rows: { key: keyof DashboardKpis; label: string; value: string }[] = [];
        (Object.keys(kpiLabelKeys) as (keyof DashboardKpis)[]).forEach((key) => {
            const v = k[key];
            if (v === null || v === undefined) {
                return;
            }
            const value = typeof v === 'number' && key === 'revenue_sar' ? v.toFixed(2) : String(v);
            rows.push({
                key,
                label: t(kpiLabelKeys[key]),
                value,
            });
        });

        return rows;
    });

    const activityRows = computed<ActivityLogRow[]>(() => activity.value?.data ?? []);

    onMounted(async () => {
        loading.value = true;
        try {
            const [o, a] = await Promise.all([fetchOverview(), fetchRecentActivity(10)]);
            overview.value = o;
            activity.value = a;
        } finally {
            loading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                {{ $t('nav.dashboard') }}
            </h1>
            <p class="mt-1 text-sm text-[#666666]">
                {{ $t('dashboard.welcome_hint') }}
            </p>
        </div>

        <div v-if="loading" class="text-sm text-[#666666]">
            {{ $t('dashboard.loading') }}
        </div>

        <template v-else>
            <div v-if="kpiEntries.length" class="grid gap-3 sm:grid-cols-2">
                <UCard
                    v-for="row in kpiEntries"
                    :key="row.key"
                    class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
                >
                    <p class="text-xs font-medium text-[#666666]">{{ row.label }}</p>
                    <p
                        class="mt-1 text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    >
                        {{ row.value }}
                    </p>
                </UCard>
            </div>

            <UCard v-if="activityRows.length" class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <template #header>
                    <h2 class="text-base font-semibold text-[#171717] dark:text-white">
                        {{ $t('dashboard.recent_activity') }}
                    </h2>
                </template>
                <ul class="divide-y divide-[#ebebeb] dark:divide-neutral-800">
                    <li
                        v-for="item in activityRows"
                        :key="item.id"
                        class="flex flex-col gap-1 py-3 text-sm text-[#4d4d4d] first:pt-0 last:pb-0"
                    >
                        <span class="font-medium text-[#171717] dark:text-white">{{
                            item.action
                        }}</span>
                        <span class="text-xs text-[#666666]">{{ item.created_at }}</span>
                    </li>
                </ul>
            </UCard>
        </template>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-[#4d4d4d]">
                    {{ $t('dashboard.profile_cta') }}
                </p>
                <UButton :to="localePath('/profile')" color="primary" variant="solid">
                    {{ $t('shell.user.profile') }}
                </UButton>
            </div>
        </UCard>

        <UCard v-if="role === 'customer'" class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-[#4d4d4d]">
                    {{ $t('dashboard.rfq_customer_cta') }}
                </p>
                <UButton :to="localePath('/rfqs')" color="primary" variant="solid">
                    {{ $t('nav.rfqs') }}
                </UButton>
            </div>
        </UCard>

        <UCard v-if="role === 'contractor'" class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-[#4d4d4d]">
                    {{ $t('dashboard.rfq_contractor_cta') }}
                </p>
                <UButton :to="localePath('/contractor/rfqs')" color="primary" variant="solid">
                    {{ $t('nav.rfq_invitations') }}
                </UButton>
            </div>
        </UCard>
    </div>
</template>
