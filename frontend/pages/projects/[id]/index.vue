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
        budget: '' as string,
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
        const name = phaseForm.name.trim();
        if (!name) {
            return;
        }
        isPhaseSubmitting.value = true;
        try {
            await apiFetch(`/v1/projects/${projectId.value}/phases`, {
                method: 'POST',
                body: {
                    name,
                    description: phaseForm.description.trim() || null,
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
        <div>
            <h2
                class="text-xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.04em"
            >
                {{ $t('projects.overview_title') }}
            </h2>
            <p class="mt-2 text-sm text-[#4d4d4d]">
                {{ $t('projects.overview_subtitle') }}
            </p>
        </div>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <template #header>
                <div class="flex items-center justify-between gap-3">
                    <span class="text-sm font-medium text-[#171717] dark:text-white">
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
                color="error"
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
                <span class="text-sm font-medium text-[#171717] dark:text-white">{{
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

        <UModal v-model:open="isPhaseModalOpen" :close="false">
            <template #content>
                <UCard class="w-full min-w-0 max-w-lg">
                    <template #header>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 space-y-1">
                                <span class="font-medium text-[#171717] dark:text-white">
                                    {{ $t('projects.add_phase') }}
                                </span>
                                <p class="text-sm text-[#666666]">
                                    {{ $t('projects.add_phase_subtitle') }}
                                </p>
                            </div>
                            <UButton
                                color="neutral"
                                variant="ghost"
                                icon="i-heroicons-x-mark"
                                class="shrink-0"
                                :aria-label="$t('common.cancel')"
                                @click="isPhaseModalOpen = false"
                            />
                        </div>
                    </template>

                    <form
                        id="phase-create-form"
                        class="max-h-[min(65vh,32rem)] space-y-5 overflow-y-auto overscroll-contain pe-1 -me-1"
                        @submit.prevent="submitPhase"
                    >
                        <UFormField
                            :label="$t('projects.phase_name')"
                            name="name"
                            required
                            class="min-w-0"
                        >
                            <UInput
                                v-model="phaseForm.name"
                                :placeholder="$t('projects.phase_name_placeholder')"
                                autocomplete="off"
                                class="w-full"
                            />
                        </UFormField>
                        <UFormField
                            :label="$t('projects.phase_description')"
                            name="description"
                            class="min-w-0"
                        >
                            <UTextarea
                                v-model="phaseForm.description"
                                :rows="3"
                                autoresize
                                :placeholder="$t('projects.phase_description_placeholder')"
                                class="w-full min-h-20"
                            />
                        </UFormField>
                        <UFormField
                            :label="$t('projects.phase_budget')"
                            name="budget"
                            class="min-w-0"
                        >
                            <UInput
                                v-model="phaseForm.budget"
                                type="number"
                                min="0"
                                step="0.01"
                                :placeholder="$t('projects.phase_budget_placeholder')"
                                class="w-full"
                            />
                        </UFormField>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 sm:gap-4">
                            <UFormField
                                :label="$t('projects.phase_start_date')"
                                name="start_date"
                                class="min-w-0"
                            >
                                <UInput v-model="phaseForm.start_date" type="date" class="w-full" />
                            </UFormField>
                            <UFormField
                                :label="$t('projects.phase_end_date')"
                                name="end_date"
                                class="min-w-0"
                            >
                                <UInput v-model="phaseForm.end_date" type="date" class="w-full" />
                            </UFormField>
                        </div>
                    </form>

                    <template #footer>
                        <div class="flex flex-wrap justify-end gap-2">
                            <UButton
                                type="button"
                                color="neutral"
                                variant="soft"
                                @click="isPhaseModalOpen = false"
                            >
                                {{ $t('common.cancel') }}
                            </UButton>
                            <UButton
                                type="submit"
                                form="phase-create-form"
                                color="primary"
                                :loading="isPhaseSubmitting"
                                :disabled="!phaseForm.name?.trim()"
                            >
                                {{ $t('common.save') }}
                            </UButton>
                        </div>
                    </template>
                </UCard>
            </template>
        </UModal>
    </div>
</template>
