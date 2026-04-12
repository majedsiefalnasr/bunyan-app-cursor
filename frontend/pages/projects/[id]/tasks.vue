<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const { apiFetch } = useApi();

    interface TaskRow {
        id: number;
        title_ar: string | null;
        title_en: string | null;
        name: string;
        status: string;
        priority: string;
        phase_id: number;
    }

    const projectId = computed(() => String(route.params.id));
    const tasks = ref<TaskRow[]>([]);
    const isLoading = ref(true);
    const loadError = ref<string | null>(null);

    const columns = ['todo', 'in_progress', 'in_review', 'done', 'blocked'] as const;

    function tasksByStatus(status: (typeof columns)[number]): TaskRow[] {
        return tasks.value.filter((t) => t.status === status);
    }

    onMounted(async () => {
        try {
            const res = await apiFetch<{ data: TaskRow[] | { data: TaskRow[] } }>(
                `/v1/projects/${projectId.value}/tasks?per_page=100`
            );
            const payload = res.data as TaskRow[] | { data: TaskRow[] };
            tasks.value = Array.isArray(payload) ? payload : (payload?.data ?? []);
        } catch {
            loadError.value = 'load_failed';
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6">
        <UButton :to="localePath(`/projects/${projectId}`)" variant="soft" color="gray">
            {{ $t('projects.back_to_list') }}
        </UButton>

        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.06em"
            >
                {{ $t('projects.tasks_title') }}
            </h1>
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
                v-for="col in columns"
                :key="col"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
            >
                <template #header>
                    <span class="text-sm font-medium text-[#171717] dark:text-white">{{
                        col
                    }}</span>
                </template>
                <ul class="space-y-2">
                    <li
                        v-for="t in tasksByStatus(col)"
                        :key="t.id"
                        class="rounded-md bg-[#fafafa] px-2 py-1.5 text-xs text-[#171717] dark:bg-neutral-900 dark:text-white"
                    >
                        {{ t.title_ar || t.name }}
                    </li>
                    <li v-if="tasksByStatus(col).length === 0" class="text-xs text-[#666666]">—</li>
                </ul>
            </UCard>
        </div>
    </div>
</template>
