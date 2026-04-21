<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const { listTasks } = useTasks();

    interface Task {
        id: number;
        title: string;
        description?: string;
        status: string;
        priority: string;
        assigned_to?: number;
        estimated_hours?: number;
        actual_hours?: number;
        phase_id?: number;
        project_id?: number;
        created_by?: number;
        created_at?: string;
    }

    const tasks = ref<Task[]>([]);
    const isLoading = ref(true);
    const loadError = ref<string | null>(null);
    const selectedStatus = ref<string>('');
    const selectedPriority = ref<string>('');
    const searchQuery = ref<string>('');

    const statusColors: Record<string, { label: string; color: string; icon: string }> = {
        todo: { label: 'للقيام به', color: 'bg-gray-50', icon: 'i-heroicons-minus-circle' },
        in_progress: { label: 'قيد الإنجاز', color: 'bg-blue-50', icon: 'i-heroicons-arrow-path' },
        in_review: { label: 'قيد المراجعة', color: 'bg-indigo-50', icon: 'i-heroicons-eye' },
        done: { label: 'مكتمل', color: 'bg-green-50', icon: 'i-heroicons-check-circle' },
        blocked: { label: 'معلق', color: 'bg-red-50', icon: 'i-heroicons-pause-circle' },
    };

    const priorityColors: Record<string, { label: string; color: string }> = {
        low: { label: 'منخفضة', color: 'bg-green-100' },
        medium: { label: 'متوسطة', color: 'bg-yellow-100' },
        high: { label: 'عالية', color: 'bg-orange-100' },
        critical: { label: 'حرجة', color: 'bg-red-100' },
    };

    const filteredTasks = computed(() => {
        let filtered = tasks.value;

        if (selectedStatus.value) {
            filtered = filtered.filter((t) => t.status === selectedStatus.value);
        }

        if (selectedPriority.value) {
            filtered = filtered.filter((t) => t.priority === selectedPriority.value);
        }

        if (searchQuery.value) {
            const q = searchQuery.value.toLowerCase();
            filtered = filtered.filter((t) => t.title.toLowerCase().includes(q));
        }

        return filtered.sort((a, b) => {
            const priorityOrder = { critical: 4, high: 3, medium: 2, low: 1 };
            const aPriority = priorityOrder[a.priority as keyof typeof priorityOrder] || 0;
            const bPriority = priorityOrder[b.priority as keyof typeof priorityOrder] || 0;
            return bPriority - aPriority;
        });
    });

    const tasksByStatus = computed(() => {
        const result: Record<string, number> = {
            todo: 0,
            in_progress: 0,
            in_review: 0,
            done: 0,
            blocked: 0,
        };
        tasks.value.forEach((t) => {
            const status = t.status as keyof typeof result;
            if (result[status] !== undefined) {
                result[status]++;
            }
        });
        return result;
    });

    const completionPercentage = computed(() => {
        if (tasks.value.length === 0) return 0;
        return Math.round(((tasksByStatus.value.done ?? 0) / tasks.value.length) * 100);
    });

    onMounted(async () => {
        try {
            const response = (await listTasks({ per_page: 200 })) as { data?: Task[] };
            tasks.value = response.data || [];
        } catch (e: unknown) {
            tasks.value = [];
            loadError.value = e instanceof Error ? e.message : String(e);
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto w-full space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[#171717] dark:text-white">المهام</h1>
            <p class="mt-2 text-sm text-[#666666]">إدارة وتتبع جميع مهام المشاريع الخاصة بك</p>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-[#666666] mb-1">الإجمالي</p>
                        <p class="text-2xl font-bold text-[#171717]">{{ tasks.length }}</p>
                    </div>
                    <UIcon
                        name="i-heroicons-check-badge"
                        class="text-3xl text-blue-400 opacity-50"
                    />
                </div>
            </UCard>

            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-[#666666] mb-1">مكتمل</p>
                        <p class="text-2xl font-bold text-green-600">{{ tasksByStatus.done }}</p>
                    </div>
                    <UIcon
                        name="i-heroicons-check-circle"
                        class="text-3xl text-green-400 opacity-50"
                    />
                </div>
            </UCard>

            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-[#666666] mb-1">قيد الإنجاز</p>
                        <p class="text-2xl font-bold text-blue-600">
                            {{ tasksByStatus.in_progress }}
                        </p>
                    </div>
                    <UIcon
                        name="i-heroicons-arrow-path"
                        class="text-3xl text-blue-400 opacity-50"
                    />
                </div>
            </UCard>

            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-[#666666] mb-1">بانتظار</p>
                        <p class="text-2xl font-bold text-gray-600">{{ tasksByStatus.todo }}</p>
                    </div>
                    <UIcon
                        name="i-heroicons-minus-circle"
                        class="text-3xl text-gray-400 opacity-50"
                    />
                </div>
            </UCard>

            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-[#666666] mb-1">معدل الإنجاز</p>
                        <p class="text-2xl font-bold text-[#171717]">{{ completionPercentage }}%</p>
                    </div>
                    <UIcon
                        name="i-heroicons-chart-bar-square"
                        class="text-3xl text-orange-400 opacity-50"
                    />
                </div>
                <div class="mt-3 w-full bg-gray-200 rounded-full h-2">
                    <div
                        class="bg-orange-500 h-2 rounded-full transition-all duration-300"
                        :style="{ width: `${completionPercentage}%` }"
                    />
                </div>
            </UCard>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <UInput
                v-model="searchQuery"
                placeholder="ابحث عن مهمة..."
                icon="i-heroicons-magnifying-glass"
                type="search"
            />
            <USelect
                v-model="selectedStatus"
                :options="[
                    { label: 'جميع الحالات', value: '' },
                    { label: 'للقيام به', value: 'todo' },
                    { label: 'قيد الإنجاز', value: 'in_progress' },
                    { label: 'قيد المراجعة', value: 'in_review' },
                    { label: 'مكتمل', value: 'done' },
                    { label: 'معلق', value: 'blocked' },
                ]"
                placeholder="فلتر حسب الحالة"
            />
            <USelect
                v-model="selectedPriority"
                :options="[
                    { label: 'جميع الأولويات', value: '' },
                    { label: 'منخفضة', value: 'low' },
                    { label: 'متوسطة', value: 'medium' },
                    { label: 'عالية', value: 'high' },
                    { label: 'حرجة', value: 'critical' },
                ]"
                placeholder="فلتر حسب الأولوية"
            />
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="space-y-4">
            <div v-for="i in 6" :key="i">
                <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <USkeleton class="h-5 w-1/2" />
                            <USkeleton class="h-5 w-20" />
                        </div>
                        <USkeleton class="h-4 w-3/4" />
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
        <UCard v-else-if="tasks.length === 0" class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <div class="text-center py-12">
                <UIcon name="i-heroicons-inbox" class="mx-auto text-4xl text-[#d1d5db] mb-4" />
                <p class="text-lg font-semibold text-[#171717] mb-2">لا توجد مهام</p>
                <p class="text-sm text-[#666666]">ابدأ بإضافة مهمة جديدة الآن</p>
            </div>
        </UCard>

        <!-- No Results State -->
        <UCard
            v-else-if="filteredTasks.length === 0"
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

        <!-- Tasks List -->
        <div v-else class="space-y-3">
            <div v-for="task in filteredTasks" :key="task.id">
                <UCard
                    class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)] hover:shadow-[0px_0px_0px_1px_rgba(0,0,0,0.12),0px_8px_8px_rgba(0,0,0,0.08)] transition-shadow cursor-pointer"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2">
                                <div
                                    class="w-1 h-1 rounded-full"
                                    :class="{
                                        'bg-gray-400': task.status === 'todo',
                                        'bg-blue-500': task.status === 'in_progress',
                                        'bg-indigo-500': task.status === 'in_review',
                                        'bg-green-500': task.status === 'done',
                                        'bg-red-500': task.status === 'blocked',
                                    }"
                                />
                                <h3 class="text-lg font-semibold text-[#171717]">
                                    {{ task.title }}
                                </h3>
                            </div>
                            <p class="text-sm text-[#666666] mb-3">
                                {{ task.description || 'بدون وصف' }}
                            </p>
                            <div class="flex flex-wrap items-center gap-2">
                                <UBadge :class="statusColors[task.status]?.color">
                                    {{ statusColors[task.status]?.label }}
                                </UBadge>
                                <UBadge :class="priorityColors[task.priority]?.color">
                                    {{ priorityColors[task.priority]?.label }}
                                </UBadge>
                            </div>
                        </div>

                        <div class="text-right">
                            <div class="grid grid-cols-2 gap-3 text-center">
                                <div>
                                    <p class="text-xs text-[#666666]">المقدرة</p>
                                    <p class="text-sm font-semibold text-[#171717]">
                                        {{ task.estimated_hours || '-' }} ساعة
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#666666]">الفعلية</p>
                                    <p class="text-sm font-semibold text-[#171717]">
                                        {{ task.actual_hours || '-' }} ساعة
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </UCard>
            </div>
        </div>
    </div>
</template>
