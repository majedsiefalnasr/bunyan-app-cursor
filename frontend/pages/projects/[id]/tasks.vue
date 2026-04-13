<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const route = useRoute();
    const { t } = useI18n();
    const { apiFetch } = useApi();

    const projectId = computed(() => String(route.params.id));
    const tasks = ref<TaskBoardRow[]>([]);
    const isLoading = ref(true);
    const loadError = ref<string | null>(null);

    const columnLabels: Record<TaskBoardColumn, string> = {
        todo: 'projects.tasks_col_todo',
        in_progress: 'projects.tasks_col_in_progress',
        in_review: 'projects.tasks_col_in_review',
        done: 'projects.tasks_col_done',
        blocked: 'projects.tasks_col_blocked',
    };

    onMounted(async () => {
        try {
            const res = await apiFetch<{ data: unknown }>(
                `/v1/projects/${projectId.value}/tasks?per_page=100`
            );
            tasks.value = normalizeProjectTasksPayload(res.data);
        } catch {
            loadError.value = 'load_failed';
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2
                class="text-xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.04em"
            >
                {{ $t('projects.tasks_title') }}
            </h2>
            <p class="mt-2 text-sm text-[#4d4d4d]">
                {{ $t('projects.tasks_subtitle') }}
            </p>
        </div>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>
        <UAlert v-else-if="loadError" color="red" variant="soft" :title="$t('errors.retry')" />

        <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-5">
            <UCard
                v-for="col in TASK_BOARD_COLUMNS"
                :key="col"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
            >
                <template #header>
                    <span class="text-sm font-medium text-[#171717] dark:text-white">{{
                        t(columnLabels[col])
                    }}</span>
                </template>
                <ul class="space-y-2">
                    <li
                        v-for="task in tasksForColumn(tasks, col)"
                        :key="task.id"
                        class="rounded-md bg-[#fafafa] px-2 py-1.5 text-xs text-[#171717] dark:bg-neutral-900 dark:text-white"
                    >
                        {{ task.title_ar || task.name }}
                    </li>
                    <li
                        v-if="tasksForColumn(tasks, col).length === 0"
                        class="text-xs text-[#666666]"
                    >
                        —
                    </li>
                </ul>
            </UCard>
        </div>
    </div>
</template>
