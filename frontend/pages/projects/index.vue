<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const localePath = useLocalePath();
    const { apiFetch } = useApi();

    interface ProjectRow {
        id: number;
        title?: string;
        name?: string;
        status: string;
        budget?: number;
        location?: string;
        start_date: string | null;
        end_date: string | null;
        description?: string;
        progress?: number;
    }

    const projects = ref<ProjectRow[]>([]);
    const isLoading = ref(true);
    const loadError = ref<string | null>(null);
    const selectedStatus = ref<string>('');
    const searchQuery = ref<string>('');

    const statusColors: Record<string, { color: string; icon: string; bgColor: string }> = {
        draft: { color: 'bg-gray-100', icon: 'i-heroicons-document-text', bgColor: '#e5e7eb' },
        planning: {
            color: 'bg-blue-100',
            icon: 'i-heroicons-clipboard-document-list',
            bgColor: '#dbeafe',
        },
        in_progress: { color: 'bg-orange-100', icon: 'i-heroicons-arrow-path', bgColor: '#fed7aa' },
        on_hold: { color: 'bg-yellow-100', icon: 'i-heroicons-pause-circle', bgColor: '#fef3c7' },
        completed: { color: 'bg-green-100', icon: 'i-heroicons-check-circle', bgColor: '#dcfce7' },
        closed: { color: 'bg-red-100', icon: 'i-heroicons-x-circle', bgColor: '#fee2e2' },
    };

    const statusLabels: Record<string, string> = {
        draft: 'مسودة',
        planning: 'قيد التخطيط',
        in_progress: 'قيد الإنجاز',
        on_hold: 'معلق',
        completed: 'مكتمل',
        closed: 'مغلق',
    };

    const filteredProjects = computed(() => {
        let filtered = projects.value;

        if (selectedStatus.value) {
            filtered = filtered.filter((p) => p.status === selectedStatus.value);
        }

        if (searchQuery.value) {
            const q = searchQuery.value.toLowerCase();
            filtered = filtered.filter(
                (p) =>
                    (p.title || p.name || '').toLowerCase().includes(q) ||
                    (p.location || '').toLowerCase().includes(q)
            );
        }

        return filtered;
    });

    const formatBudget = (budget: number | undefined) => {
        if (!budget) return 'غير محدد';
        return new Intl.NumberFormat('ar-SA', {
            style: 'currency',
            currency: 'SAR',
            maximumFractionDigits: 0,
        }).format(budget);
    };

    onMounted(async () => {
        loadError.value = null;
        try {
            const res = await apiFetch<{ data: { data?: ProjectRow[] } | ProjectRow[] }>(
                '/v1/projects'
            );
            const payload = res.data as { data?: ProjectRow[] } | ProjectRow[];
            projects.value = Array.isArray(payload) ? payload : (payload.data ?? []);
        } catch (e: unknown) {
            projects.value = [];
            loadError.value = e instanceof Error ? e.message : String(e);
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto w-full space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold tracking-tight text-[#171717] dark:text-white">
                    المشاريع
                </h1>
                <p class="mt-2 text-sm text-[#666666]">إدارة وتتبع جميع مشاريع البناء الخاصة بك</p>
            </div>
            <UButton :to="localePath('/projects/create')" color="primary" variant="solid" size="lg">
                <template #leading>
                    <UIcon name="i-heroicons-plus" />
                </template>
                إنشاء مشروع جديد
            </UButton>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <UInput
                v-model="searchQuery"
                placeholder="ابحث عن مشروع..."
                icon="i-heroicons-magnifying-glass"
                type="search"
            />
            <USelect
                v-model="selectedStatus"
                :options="[
                    { label: 'جميع الحالات', value: '' },
                    { label: 'مسودة', value: 'draft' },
                    { label: 'قيد التخطيط', value: 'planning' },
                    { label: 'قيد الإنجاز', value: 'in_progress' },
                    { label: 'معلق', value: 'on_hold' },
                    { label: 'مكتمل', value: 'completed' },
                    { label: 'مغلق', value: 'closed' },
                ]"
                placeholder="فلتر حسب الحالة"
            />
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 3" :key="i">
                <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                    <div class="space-y-3">
                        <USkeleton class="h-6 w-40" />
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
        <UCard v-else-if="projects.length === 0" class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="text-center py-12">
                <UIcon
                    name="i-heroicons-building-office-2"
                    class="mx-auto text-4xl text-[#d1d5db] mb-4"
                />
                <p class="text-lg font-semibold text-[#171717] mb-2">لا توجد مشاريع</p>
                <p class="text-sm text-[#666666] mb-4">ابدأ بإنشاء مشروع جديد الآن</p>
                <UButton :to="localePath('/projects/create')" color="primary" variant="solid">
                    إنشاء مشروع جديد
                </UButton>
            </div>
        </UCard>

        <!-- No Results State -->
        <UCard
            v-else-if="filteredProjects.length === 0"
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

        <!-- Projects Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <UCard
                v-for="project in filteredProjects"
                :key="project.id"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] hover:shadow-[0px_0px_0px_1px_rgba(0,0,0,0.12),0px_8px_8px_rgba(0,0,0,0.08)] transition-shadow"
            >
                <!-- Status Badge -->
                <template #header>
                    <div class="flex items-start justify-between gap-2">
                        <div class="space-y-2 flex-1">
                            <h3
                                class="text-lg font-semibold text-[#171717] dark:text-white line-clamp-2"
                            >
                                {{ project.title || project.name }}
                            </h3>
                            <p
                                v-if="project.location"
                                class="text-sm text-[#666666] flex items-center gap-1"
                            >
                                <UIcon name="i-heroicons-map-pin" class="text-base" />
                                {{ project.location }}
                            </p>
                        </div>
                        <UBadge
                            :color="statusColors[project.status]?.color || 'bg-gray-100'"
                            variant="soft"
                            size="md"
                        >
                            {{ statusLabels[project.status] || project.status }}
                        </UBadge>
                    </div>
                </template>

                <!-- Content -->
                <div class="space-y-4">
                    <!-- Description -->
                    <p v-if="project.description" class="text-sm text-[#4d4d4d] line-clamp-2">
                        {{ project.description }}
                    </p>

                    <!-- Budget -->
                    <div class="flex items-center justify-between pt-2 border-t border-[#ebebeb]">
                        <span class="text-sm text-[#666666]">الميزانية:</span>
                        <span class="font-semibold text-[#171717]">{{
                            formatBudget(project.budget)
                        }}</span>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <p class="text-[#666666] mb-1">تاريخ البدء</p>
                            <p class="font-medium text-[#171717]">
                                {{
                                    project.start_date
                                        ? new Date(project.start_date).toLocaleDateString('ar-SA')
                                        : 'غير محدد'
                                }}
                            </p>
                        </div>
                        <div>
                            <p class="text-[#666666] mb-1">تاريخ الانتهاء</p>
                            <p class="font-medium text-[#171717]">
                                {{
                                    project.end_date
                                        ? new Date(project.end_date).toLocaleDateString('ar-SA')
                                        : 'غير محدد'
                                }}
                            </p>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div v-if="project.progress" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-[#666666]">التقدم</span>
                            <span class="text-sm font-semibold text-[#171717]"
                                >{{ project.progress }}%</span
                            >
                        </div>
                        <div class="w-full bg-[#ebebeb] rounded-full h-2 overflow-hidden">
                            <div
                                class="bg-gradient-to-r from-blue-500 to-blue-600 h-full transition-all"
                                :style="{ width: `${project.progress}%` }"
                            />
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2">
                        <UButton
                            :to="localePath(`/projects/${project.id}`)"
                            variant="ghost"
                            color="gray"
                            class="w-full"
                        >
                            عرض التفاصيل
                            <template #trailing>
                                <UIcon name="i-heroicons-arrow-left" />
                            </template>
                        </UButton>
                    </div>
                </div>
            </UCard>
        </div>

        <!-- Summary Stats -->
        <div
            v-if="filteredProjects.length > 0"
            class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-[#ebebeb]"
        >
            <div class="text-center">
                <p class="text-2xl font-bold text-[#171717]">{{ filteredProjects.length }}</p>
                <p class="text-sm text-[#666666]">المشاريع</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-green-600">
                    {{ filteredProjects.filter((p) => p.status === 'completed').length }}
                </p>
                <p class="text-sm text-[#666666]">مكتملة</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-orange-600">
                    {{ filteredProjects.filter((p) => p.status === 'in_progress').length }}
                </p>
                <p class="text-sm text-[#666666]">قيد الإنجاز</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-blue-600">
                    {{
                        Math.round(
                            filteredProjects.reduce((sum, p) => sum + (p.budget || 0), 0) / 1000000
                        )
                    }}M
                </p>
                <p class="text-sm text-[#666666]">إجمالي الميزانية</p>
            </div>
        </div>
    </div>
</template>
