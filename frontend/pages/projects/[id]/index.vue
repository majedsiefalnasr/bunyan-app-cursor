<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const route = useRoute();
    const { apiFetch } = useApi();

    interface TimelinePhase {
        id: number;
        name: string;
        completion_percentage: number;
        sort_order: number;
    }

    const projectShell = inject(PROJECT_SHELL_KEY)!;
    const timelinePhases = ref<TimelinePhase[]>([]);
    const isTimelineLoading = ref(true);

    const projectId = computed(() => String(route.params.id));

    watch(
        () => projectShell.value?.id,
        async (id) => {
            if (!id) {
                return;
            }
            isTimelineLoading.value = true;
            try {
                const tRes = await apiFetch<{ data: { phases: TimelinePhase[] } }>(
                    `/v1/projects/${projectId.value}/timeline`
                );
                timelinePhases.value = tRes.data?.phases ?? [];
            } finally {
                isTimelineLoading.value = false;
            }
        },
        { immediate: true }
    );
</script>

<template>
    <div class="space-y-6">
        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <template #header>
                <span class="font-medium text-[#171717] dark:text-white">{{
                    $t('projects.timeline_title')
                }}</span>
            </template>
            <div v-if="isTimelineLoading" class="text-sm text-[#666666]">
                {{ $t('shell.loading') }}
            </div>
            <p v-else-if="timelinePhases.length === 0" class="text-sm text-[#666666]">
                {{ $t('projects.phases_heading') }}: —
            </p>
            <ul v-else class="space-y-2 text-sm text-[#4d4d4d]">
                <li v-for="ph in timelinePhases" :key="ph.id">
                    {{ ph.name }} — {{ ph.completion_percentage }}%
                </li>
            </ul>
        </UCard>

        <ActivityTimeline v-if="projectShell" entity="projects" :subject-id="projectShell.id" />
    </div>
</template>
