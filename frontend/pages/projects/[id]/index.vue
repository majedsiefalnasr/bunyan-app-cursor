<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const route = useRoute();
    const { apiFetch } = useApi();
    const auth = useAuthStore();

    interface TimelinePhase {
        id: number;
        name: string;
        completion_percentage: number;
        sort_order: number;
    }

    interface Phase {
        id: number;
        name: string;
        description?: string | null;
        status?: string;
        budget?: number | null;
        start_date?: string | null;
        end_date?: string | null;
    }

    const projectShell = inject(PROJECT_SHELL_KEY)!;
    const timelinePhases = ref<TimelinePhase[]>([]);
    const isTimelineLoading = ref(true);

    const projectId = computed(() => String(route.params.id));

    const phases = ref<Phase[]>([]);
    const isPhasesLoading = ref(false);
    const phasesLoadError = ref<string | null>(null);
    const isPhaseModalOpen = ref(false);
    const isPhaseSubmitting = ref(false);
    const phaseForm = reactive({
        name: '',
        description: '',
        budget: '' as string | number,
        start_date: '',
        end_date: '',
    });

    async function fetchPhases() {
        isPhasesLoading.value = true;
        phasesLoadError.value = null;
        try {
            const res = await apiFetch<{ data: Phase[] }>(`/v1/projects/${projectId.value}/phases`);
            phases.value = res.data ?? [];
        } catch (e: unknown) {
            phases.value = [];
            phasesLoadError.value = e instanceof Error ? e.message : String(e);
        } finally {
            isPhasesLoading.value = false;
        }
    }

    const canManagePhases = computed(() => {
        const role = auth.user?.role;
        if (!role) return false;
        return role === 'admin' || role === 'customer' || role === 'contractor';
    });

    function openCreatePhase() {
        if (!canManagePhases.value) return;
        phaseForm.name = '';
        phaseForm.description = '';
        phaseForm.budget = '';
        phaseForm.start_date = '';
        phaseForm.end_date = '';
        isPhaseModalOpen.value = true;
    }

    async function submitPhase() {
        if (!phaseForm.name) return;
        isPhaseSubmitting.value = true;
        try {
            await apiFetch(`/v1/projects/${projectId.value}/phases`, {
                method: 'POST',
                body: {
                    name: phaseForm.name,
                    description: phaseForm.description || null,
                    budget: phaseForm.budget === '' ? null : Number(phaseForm.budget),
                    start_date: phaseForm.start_date || null,
                    end_date: phaseForm.end_date || null,
                },
            });
            isPhaseModalOpen.value = false;
            await fetchPhases();
            // also refresh timeline list
            const tRes = await apiFetch<{ data: { phases: TimelinePhase[] } }>(
                `/v1/projects/${projectId.value}/timeline`
            );
            timelinePhases.value = tRes.data?.phases ?? [];
        } finally {
            isPhaseSubmitting.value = false;
        }
    }

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
                await fetchPhases();
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
                <div class="flex items-center justify-between gap-3">
                    <span class="font-medium text-[#171717] dark:text-white">
                        {{ $t('projects.phases_heading') }}
                    </span>
                    <UButton
                        v-if="canManagePhases"
                        size="sm"
                        color="primary"
                        variant="solid"
                        @click="openCreatePhase"
                    >
                        {{ $t('projects.add_phase') }}
                    </UButton>
                </div>
            </template>

            <div v-if="isPhasesLoading" class="text-sm text-[#666666]">
                {{ $t('shell.loading') }}
            </div>
            <UAlert
                v-else-if="phasesLoadError"
                color="red"
                variant="soft"
                :title="$t('shell.error')"
                :description="phasesLoadError"
            />
            <p v-else-if="phases.length === 0" class="text-sm text-[#666666]">
                {{ $t('projects.no_phases_yet') }}
            </p>
            <ul v-else class="space-y-2 text-sm text-[#4d4d4d]">
                <li
                    v-for="ph in phases"
                    :key="ph.id"
                    class="flex items-center justify-between gap-4"
                >
                    <div class="min-w-0">
                        <p class="truncate font-medium text-[#171717] dark:text-white">
                            {{ ph.name }}
                        </p>
                        <p
                            v-if="ph.budget !== null && ph.budget !== undefined"
                            class="text-xs text-[#666666]"
                        >
                            {{ $t('projects.phase_budget') }}: {{ ph.budget }}
                        </p>
                    </div>
                    <span class="text-xs text-[#666666]">
                        {{ ph.status || '—' }}
                    </span>
                </li>
            </ul>
        </UCard>

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

        <UModal v-model="isPhaseModalOpen">
            <UCard>
                <template #header>
                    <span class="font-medium text-[#171717] dark:text-white">
                        {{ $t('projects.add_phase') }}
                    </span>
                </template>

                <div class="space-y-4">
                    <UFormGroup :label="$t('projects.phase_name')" name="name">
                        <UInput v-model="phaseForm.name" />
                    </UFormGroup>
                    <UFormGroup :label="$t('projects.phase_description')" name="description">
                        <UTextarea v-model="phaseForm.description" :rows="3" />
                    </UFormGroup>
                    <UFormGroup :label="$t('projects.phase_budget')" name="budget">
                        <UInput v-model="phaseForm.budget" type="number" min="0" step="0.01" />
                    </UFormGroup>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <UFormGroup :label="$t('projects.phase_start_date')" name="start_date">
                            <UInput v-model="phaseForm.start_date" type="date" />
                        </UFormGroup>
                        <UFormGroup :label="$t('projects.phase_end_date')" name="end_date">
                            <UInput v-model="phaseForm.end_date" type="date" />
                        </UFormGroup>
                    </div>

                    <div class="flex justify-end gap-2">
                        <UButton color="gray" variant="soft" @click="isPhaseModalOpen = false">
                            {{ $t('common.cancel') }}
                        </UButton>
                        <UButton
                            color="primary"
                            :loading="isPhaseSubmitting"
                            :disabled="!phaseForm.name"
                            @click="submitPhase"
                        >
                            {{ $t('common.save') }}
                        </UButton>
                    </div>
                </div>
            </UCard>
        </UModal>
    </div>
</template>
