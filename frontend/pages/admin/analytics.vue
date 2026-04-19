<script setup lang="ts">
    definePageMeta({
        layout: 'admin',
        middleware: ['auth', 'role'],
        roles: ['admin', 'supervising_architect'],
    });

    const { overview, trends } = useAnalyticsApi();

    const bucket = ref<'day' | 'week' | 'month'>('day');
    const compare = ref<'none' | 'previous_period' | 'previous_year'>('previous_period');
    const from = ref<string>('');
    const to = ref<string>('');

    const isLoading = ref(false);
    const overviewData = ref<import('~/types/analytics').AnalyticsOverviewResponse['data'] | null>(
        null
    );
    const trendsData = ref<import('~/types/analytics').AnalyticsTrendsResponse['data'] | null>(
        null
    );

    async function load() {
        isLoading.value = true;
        try {
            const res = await overview({
                bucket: bucket.value,
                compare: compare.value,
                from: from.value || undefined,
                to: to.value || undefined,
            });
            overviewData.value = res.data;

            const tr = await trends({
                bucket: bucket.value,
                keys: [
                    'commerce.gmv',
                    'commerce.order_volume',
                    'platform.active_users',
                    'projects.new_projects',
                ],
                from: from.value || undefined,
                to: to.value || undefined,
            });
            trendsData.value = tr.data;
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(() => {
        void load();
    });
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight" style="letter-spacing: -0.06em">
                التحليلات
            </h1>
            <p class="mt-1 text-sm text-gray-600">مؤشرات المنصة واتجاهات الأداء</p>
        </div>

        <div class="rounded-lg bg-white p-4 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <UFormGroup label="من">
                    <UInput v-model="from" type="date" />
                </UFormGroup>
                <UFormGroup label="إلى">
                    <UInput v-model="to" type="date" />
                </UFormGroup>
                <UFormGroup label="التجميع">
                    <USelect
                        v-model="bucket"
                        :items="[
                            { value: 'day', label: 'يومي' },
                            { value: 'week', label: 'أسبوعي' },
                            { value: 'month', label: 'شهري' },
                        ]"
                    />
                </UFormGroup>
                <UFormGroup label="المقارنة">
                    <USelect
                        v-model="compare"
                        :items="[
                            { value: 'none', label: 'بدون' },
                            { value: 'previous_period', label: 'الفترة السابقة' },
                            { value: 'previous_year', label: 'السنة السابقة' },
                        ]"
                    />
                </UFormGroup>
            </div>

            <div class="flex items-center gap-2">
                <UButton :loading="isLoading" @click="load">تحديث</UButton>
                <span v-if="overviewData?.range" class="text-xs text-gray-500">
                    {{ overviewData.range.from }} → {{ overviewData.range.to }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <UCard v-for="kpi in overviewData?.kpis || []" :key="kpi.key">
                <template #header>
                    <div class="text-sm font-medium text-gray-600">{{ kpi.key }}</div>
                </template>
                <div class="text-2xl font-semibold tracking-tight" style="letter-spacing: -0.04em">
                    {{ kpi.value ?? '—' }}
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    Δ {{ kpi.delta?.value ?? '—' }} ({{ kpi.delta?.pct ?? '—' }})
                </div>
            </UCard>
        </div>

        <UCard>
            <template #header>
                <div class="text-sm font-medium text-gray-600">الاتجاهات</div>
            </template>
            <pre class="text-xs overflow-x-auto">{{ JSON.stringify(trendsData, null, 2) }}</pre>
        </UCard>
    </div>
</template>
