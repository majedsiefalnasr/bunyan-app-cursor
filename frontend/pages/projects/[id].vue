<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const { apiFetch } = useApi();

    interface ProjectDetail {
        id: number;
        name: string;
        status: string;
        start_date: string | null;
        end_date: string | null;
    }

    interface TimelinePhase {
        id: number;
        name: string;
        completion_percentage: number;
        sort_order: number;
    }

    const project = ref<ProjectDetail | null>(null);
    const timelinePhases = ref<TimelinePhase[]>([]);
    const isLoading = ref(true);

    onMounted(async () => {
        const id = route.params.id;
        try {
            const [pRes, tRes] = await Promise.all([
                apiFetch<{ data: ProjectDetail }>(`/v1/projects/${id}`),
                apiFetch<{ data: { phases: TimelinePhase[] } }>(`/v1/projects/${id}/timeline`),
            ]);
            project.value = pRes.data ?? null;
            timelinePhases.value = tRes.data?.phases ?? [];
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex flex-wrap gap-2">
            <UButton :to="localePath('/projects')" variant="soft" color="gray">
                {{ $t('projects.back_to_list') }}
            </UButton>
            <UButton
                :to="localePath(`/projects/${route.params.id}/tasks`)"
                variant="soft"
                color="gray"
            >
                {{ $t('projects.open_tasks') }}
            </UButton>
        </div>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <template v-else-if="project">
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.06em"
                >
                    {{ project.name }}
                </h1>
                <p class="mt-2 text-sm text-[#4d4d4d]">
                    {{ $t('projects.status_label') }}:
                    <UBadge class="ms-1" color="gray" variant="soft">{{ project.status }}</UBadge>
                </p>
            </div>

            <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                <template #header>
                    <span class="font-medium text-[#171717] dark:text-white">{{
                        $t('projects.timeline_title')
                    }}</span>
                </template>
                <p v-if="timelinePhases.length === 0" class="text-sm text-[#666666]">
                    {{ $t('projects.phases_heading') }}: —
                </p>
                <ul v-else class="space-y-2 text-sm text-[#4d4d4d]">
                    <li v-for="ph in timelinePhases" :key="ph.id">
                        {{ ph.name }} — {{ ph.completion_percentage }}%
                    </li>
                </ul>
            </UCard>

            <ActivityTimeline v-if="project" entity="projects" :subject-id="project.id" />
        </template>
    </div>
</template>
