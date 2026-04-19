<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const { listReports } = useReports();

    interface ReportRow {
        id: number;
        title: string;
        content?: string;
        description?: string;
        type: 'progress' | 'inspection';
        status: 'submitted' | 'reviewed' | 'approved';
        created_at: string;
        task_id?: number;
        phase_id?: number;
        project_id?: number;
        created_by?: number;
    }

    const reports = ref<ReportRow[]>([]);
    const isLoading = ref(true);
    const loadError = ref<string | null>(null);
    const selectedType = ref<string>('');
    const selectedStatus = ref<string>('');
    const searchQuery = ref<string>('');

    const typeColors: Record<string, { label: string; color: string; icon: string }> = {
        progress: { label: 'تقرير تقدم', color: 'bg-blue-50', icon: 'i-heroicons-arrow-path' },
        inspection: {
            label: 'تقرير فحص',
            color: 'bg-purple-50',
            icon: 'i-heroicons-clipboard-document-check',
        },
    };

    const statusColors: Record<string, { label: string; color: string; icon: string }> = {
        submitted: { label: 'مرسل', color: 'bg-yellow-50', icon: 'i-heroicons-inbox' },
        reviewed: { label: 'تم المراجعة', color: 'bg-blue-50', icon: 'i-heroicons-eye' },
        approved: { label: 'موافق عليه', color: 'bg-green-50', icon: 'i-heroicons-check-circle' },
    };

    const filteredReports = computed(() => {
        let filtered = reports.value;

        if (selectedType.value) {
            filtered = filtered.filter((r) => r.type === selectedType.value);
        }

        if (selectedStatus.value) {
            filtered = filtered.filter((r) => r.status === selectedStatus.value);
        }

        if (searchQuery.value) {
            const q = searchQuery.value.toLowerCase();
            filtered = filtered.filter(
                (r) =>
                    r.title.toLowerCase().includes(q) || (r.content || '').toLowerCase().includes(q)
            );
        }

        return filtered.sort((a, b) => {
            const dateA = new Date(a.created_at).getTime();
            const dateB = new Date(b.created_at).getTime();
            return dateB - dateA;
        });
    });

    async function load() {
        isLoading.value = true;
        loadError.value = null;
        try {
            const res = await listReports({ per_page: 100 });
            reports.value = res.data || [];
        } catch (e: unknown) {
            reports.value = [];
            loadError.value = e instanceof Error ? e.message : String(e);
        } finally {
            isLoading.value = false;
        }
    }

    onMounted(load);
</script>

<template>
    <div class="mx-auto w-full space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[#171717] dark:text-white">
                التقارير
            </h1>
            <p class="mt-2 text-sm text-[#666666]">عرض وإدارة جميع تقارير المشاريع والفحوصات</p>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <UInput
                v-model="searchQuery"
                placeholder="ابحث عن تقرير..."
                icon="i-heroicons-magnifying-glass"
                type="search"
            />
            <USelect
                v-model="selectedType"
                :options="[
                    { label: 'جميع الأنواع', value: '' },
                    { label: 'تقرير تقدم', value: 'progress' },
                    { label: 'تقرير فحص', value: 'inspection' },
                ]"
                placeholder="فلتر حسب النوع"
            />
            <USelect
                v-model="selectedStatus"
                :options="[
                    { label: 'جميع الحالات', value: '' },
                    { label: 'مرسل', value: 'submitted' },
                    { label: 'تم المراجعة', value: 'reviewed' },
                    { label: 'موافق عليه', value: 'approved' },
                ]"
                placeholder="فلتر حسب الحالة"
            />
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 5" :key="i">
                <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <USkeleton class="h-5 w-40" />
                            <USkeleton class="h-5 w-20" />
                        </div>
                        <USkeleton class="h-4 w-3/4" />
                        <USkeleton class="h-4 w-1/2" />
                    </div>
                </UCard>
            </div>
        </div>

        <!-- Error State -->
        <UAlert
            v-else-if="loadError"
            color="error"
            variant="soft"
            title="خطأ"
            :description="loadError"
        />

        <!-- Empty State -->
        <UCard v-else-if="reports.length === 0" class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="text-center py-12">
                <UIcon
                    name="i-heroicons-clipboard-document"
                    class="mx-auto text-4xl text-[#d1d5db] mb-4"
                />
                <p class="text-lg font-semibold text-[#171717] mb-2">لا توجد تقارير</p>
                <p class="text-sm text-[#666666]">سيظهر التقارير هنا عند إنشاء المهام والتقارير</p>
            </div>
        </UCard>

        <!-- No Results State -->
        <UCard
            v-else-if="filteredReports.length === 0"
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
        >
            <div class="text-center py-12">
                <UIcon
                    name="i-heroicons-magnifying-glass"
                    class="mx-auto text-4xl text-[#d1d5db] mb-4"
                />
                <p class="text-lg font-semibold text-[#171717] mb-2">لا توجد نتائج</p>
                <p class="text-sm text-[#666666]">حاول تغيير معايير البحث</p>
            </div>
        </UCard>

        <!-- Reports List -->
        <div v-else class="space-y-4">
            <div v-for="report in filteredReports" :key="report.id">
                <UCard
                    class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] hover:shadow-[0px_0px_0px_1px_rgba(0,0,0,0.12),0px_8px_8px_rgba(0,0,0,0.08)] transition-shadow"
                >
                    <div class="space-y-4">
                        <!-- Header -->
                        <div
                            class="flex items-start justify-between gap-4 pb-4 border-b border-[#ebebeb]"
                        >
                            <div class="flex-1 min-w-0">
                                <h3
                                    class="text-lg font-semibold text-[#171717] dark:text-white mb-2"
                                >
                                    {{ report.title }}
                                </h3>
                                <div class="flex flex-wrap gap-2">
                                    <UBadge
                                        :class="typeColors[report.type].color"
                                        variant="soft"
                                        size="sm"
                                    >
                                        {{ typeColors[report.type].label }}
                                    </UBadge>
                                    <UBadge
                                        :class="statusColors[report.status].color"
                                        variant="soft"
                                        size="sm"
                                    >
                                        {{ statusColors[report.status].label }}
                                    </UBadge>
                                </div>
                            </div>
                            <p class="text-sm text-[#666666] text-right whitespace-nowrap">
                                {{ new Date(report.created_at).toLocaleDateString('ar-SA') }}
                            </p>
                        </div>

                        <!-- Description -->
                        <p
                            v-if="report.description || report.content"
                            class="text-sm text-[#4d4d4d] line-clamp-2"
                        >
                            {{ report.description || report.content }}
                        </p>

                        <!-- Info Row -->
                        <div
                            class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-2 border-t border-[#f0f0f0]"
                        >
                            <div>
                                <p class="text-xs text-[#666666] mb-1">النوع</p>
                                <p class="text-sm font-medium text-[#171717]">
                                    {{ typeColors[report.type].label }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-[#666666] mb-1">الحالة</p>
                                <p class="text-sm font-medium text-[#171717]">
                                    {{ statusColors[report.status].label }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-[#666666] mb-1">التاريخ</p>
                                <p class="text-sm font-medium text-[#171717]">
                                    {{ new Date(report.created_at).toLocaleDateString('ar-SA') }}
                                </p>
                            </div>
                            <div class="text-left">
                                <UButton variant="ghost" color="gray" size="sm">
                                    عرض التفاصيل
                                    <template #trailing>
                                        <UIcon name="i-heroicons-arrow-left" />
                                    </template>
                                </UButton>
                            </div>
                        </div>
                    </div>
                </UCard>
            </div>
        </div>

        <!-- Summary Stats -->
        <div
            v-if="filteredReports.length > 0"
            class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-[#ebebeb]"
        >
            <div class="text-center">
                <p class="text-2xl font-bold text-[#171717]">{{ filteredReports.length }}</p>
                <p class="text-sm text-[#666666]">عدد التقارير</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-green-600">
                    {{ filteredReports.filter((r) => r.status === 'approved').length }}
                </p>
                <p class="text-sm text-[#666666]">موافق عليها</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-600">
                    {{ filteredReports.filter((r) => r.type === 'progress').length }}
                </p>
                <p class="text-sm text-[#666666]">تقارير التقدم</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-purple-600">
                    {{ filteredReports.filter((r) => r.type === 'inspection').length }}
                </p>
                <p class="text-sm text-[#666666]">تقارير الفحص</p>
            </div>
        </div>
    </div>
</template>
