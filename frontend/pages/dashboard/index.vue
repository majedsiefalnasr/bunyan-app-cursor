<script setup lang="ts">
    import type {
        ActivityLogRow,
        DashboardOverview,
        PaginatedActivity,
    } from '~/composables/useDashboard';

    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const localePath = useLocalePath();
    const { fetchOverview, fetchRecentActivity } = useDashboard();

    const overview = ref<DashboardOverview | null>(null);
    const activity = ref<PaginatedActivity | null>(null);
    const loading = ref(true);

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
    <div class="mx-auto w-full space-y-8">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[#171717] dark:text-white">
                {{ $t('nav.dashboard') }}
            </h1>
            <p class="mt-2 text-sm text-[#666666]">
                {{ $t('dashboard.welcome_hint') }}
            </p>
        </div>

        <div v-if="loading" class="space-y-6">
            <!-- Stats Grid Skeleton -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <UCard
                    v-for="i in 4"
                    :key="i"
                    class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]"
                >
                    <div class="space-y-3">
                        <USkeleton class="h-3 w-24" />
                        <USkeleton class="h-8 w-20" />
                    </div>
                </UCard>
            </div>

            <!-- Charts Grid Skeleton -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                    <div class="space-y-3 h-80">
                        <USkeleton class="h-5 w-40" />
                        <USkeleton class="h-64 w-full" />
                    </div>
                </UCard>
                <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                    <div class="space-y-3 h-80">
                        <USkeleton class="h-5 w-40" />
                        <USkeleton class="h-64 w-full" />
                    </div>
                </UCard>
            </div>

            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <div class="space-y-3">
                    <USkeleton class="h-5 w-40" />
                    <div class="space-y-3">
                        <div v-for="i in 5" :key="i" class="space-y-2">
                            <USkeleton class="h-4 w-3/4" />
                            <USkeleton class="h-3 w-32" />
                        </div>
                    </div>
                </div>
            </UCard>
        </div>

        <template v-else>
            <!-- Enhanced KPI Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    icon="i-heroicons-building-office-2"
                    label="إجمالي المشاريع"
                    value="7"
                    :trend="12"
                    trend-label="زيادة عن الشهر الماضي"
                    bg-color="bg-blue-50"
                    text-color="text-blue-600"
                />
                <StatCard
                    icon="i-heroicons-document-duplicate"
                    label="الطلبات النشطة"
                    value="13"
                    :trend="8"
                    trend-label="زيادة عن الشهر الماضي"
                    bg-color="bg-green-50"
                    text-color="text-green-600"
                />
                <StatCard
                    icon="i-heroicons-banknotes"
                    label="إجمالي الإيرادات"
                    value="5.2M"
                    :trend="15"
                    trend-label="زيادة عن الشهر الماضي"
                    bg-color="bg-emerald-50"
                    text-color="text-emerald-600"
                />
                <StatCard
                    icon="i-heroicons-users"
                    label="عدد المستخدمين"
                    value="16"
                    :trend="25"
                    trend-label="زيادة عن الشهر الماضي"
                    bg-color="bg-purple-50"
                    text-color="text-purple-600"
                />
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <RevenueChart />
                <ProjectStatusChart />
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <OrderDistributionChart />
                <TaskProgressChart />
            </div>

            <!-- Recent Activity -->
            <UCard v-if="activityRows.length" class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <template #header>
                    <div class="flex items-center justify-between">
                        <h2 class="text-base font-semibold text-[#171717] dark:text-white">
                            {{ $t('dashboard.recent_activity') }}
                        </h2>
                        <UButton
                            variant="ghost"
                            color="gray"
                            size="sm"
                            label="عرض الكل"
                            :to="localePath('/notifications')"
                        />
                    </div>
                </template>
                <ul class="divide-y divide-[#ebebeb] dark:divide-neutral-800">
                    <li
                        v-for="item in activityRows.slice(0, 8)"
                        :key="item.id"
                        class="flex flex-col gap-1 py-3 text-sm text-[#4d4d4d] first:pt-0 last:pb-0 hover:bg-[#fafafa] dark:hover:bg-neutral-900 px-2 -mx-2 rounded transition"
                    >
                        <span class="font-medium text-[#171717] dark:text-white">{{
                            item.action
                        }}</span>
                        <span class="text-xs text-[#666666]">{{ item.created_at }}</span>
                    </li>
                </ul>
            </UCard>

            <!-- Quick Actions -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-col gap-3">
                        <h3 class="font-semibold text-[#171717] dark:text-white">إدارة المشاريع</h3>
                        <p class="text-sm text-[#666666]">عرض وإدارة جميع المشاريع</p>
                        <div class="pt-2">
                            <UButton
                                :to="localePath('/projects')"
                                color="primary"
                                variant="solid"
                                size="sm"
                            >
                                عرض المشاريع
                            </UButton>
                        </div>
                    </div>
                </UCard>

                <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-col gap-3">
                        <h3 class="font-semibold text-[#171717] dark:text-white">المهام</h3>
                        <p class="text-sm text-[#666666]">متابعة وإدارة المهام</p>
                        <div class="pt-2">
                            <UButton
                                :to="localePath('/tasks')"
                                color="primary"
                                variant="solid"
                                size="sm"
                            >
                                عرض المهام
                            </UButton>
                        </div>
                    </div>
                </UCard>

                <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-col gap-3">
                        <h3 class="font-semibold text-[#171717] dark:text-white">الطلبات</h3>
                        <p class="text-sm text-[#666666]">متابعة وإدارة الطلبات</p>
                        <div class="pt-2">
                            <UButton
                                :to="localePath('/orders')"
                                color="primary"
                                variant="solid"
                                size="sm"
                            >
                                عرض الطلبات
                            </UButton>
                        </div>
                    </div>
                </UCard>

                <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                    <div class="flex flex-col gap-3">
                        <h3 class="font-semibold text-[#171717] dark:text-white">
                            العمليات المالية
                        </h3>
                        <p class="text-sm text-[#666666]">متابعة التحويلات والمدفوعات</p>
                        <div class="pt-2">
                            <UButton
                                :to="localePath('/transactions')"
                                color="primary"
                                variant="solid"
                                size="sm"
                            >
                                عرض العمليات
                            </UButton>
                        </div>
                    </div>
                </UCard>
            </div>
        </template>
    </div>
</template>
