<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const { apiFetch } = useApi();
    const { hasRole } = useAuth();
    const toast = useToast();
    const { t } = useI18n();

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

    interface TeamMemberRow {
        id: number;
        project_role: string;
        project_role_label: string;
        user: { id: number; name: string; email: string };
    }

    interface TeamInvitationRow {
        id: number;
        email: string;
        project_role: string;
        project_role_label: string;
    }

    const project = ref<ProjectDetail | null>(null);
    const timelinePhases = ref<TimelinePhase[]>([]);
    const teamMembers = ref<TeamMemberRow[]>([]);
    const teamInvitations = ref<TeamInvitationRow[]>([]);
    const teamLoadError = ref(false);
    const inviteEmail = ref('');
    const inviteRole = ref<'manager' | 'engineer' | 'worker' | 'viewer'>('engineer');
    const isTeamLoading = ref(false);
    const isInviteSubmitting = ref(false);
    const isLoading = ref(true);

    const canManageTeam = computed(() =>
        hasRole('customer', 'contractor', 'supervising_architect', 'admin')
    );

    const inviteRoleOptions = computed(() => [
        { value: 'manager', label: t('projects.team_role_manager') },
        { value: 'engineer', label: t('projects.team_role_engineer') },
        { value: 'worker', label: t('projects.team_role_worker') },
        { value: 'viewer', label: t('projects.team_role_viewer') },
    ]);

    async function loadTeam(projectId: string | string[]) {
        isTeamLoading.value = true;
        teamLoadError.value = false;
        try {
            const res = await apiFetch<{
                data: { members: TeamMemberRow[]; invitations_pending: TeamInvitationRow[] };
            }>(`/v1/projects/${projectId}/team`);
            teamMembers.value = res.data?.members ?? [];
            teamInvitations.value = res.data?.invitations_pending ?? [];
        } catch {
            teamLoadError.value = true;
        } finally {
            isTeamLoading.value = false;
        }
    }

    async function submitInvite(projectId: string | string[]) {
        if (!inviteEmail.value.trim()) {
            return;
        }
        isInviteSubmitting.value = true;
        try {
            await apiFetch(`/v1/projects/${projectId}/team`, {
                method: 'POST',
                body: {
                    email: inviteEmail.value.trim().toLowerCase(),
                    project_role: inviteRole.value,
                },
            });
            inviteEmail.value = '';
            toast.add({ title: t('projects.team_invite_success'), color: 'green' });
            await loadTeam(projectId);
        } finally {
            isInviteSubmitting.value = false;
        }
    }

    onMounted(async () => {
        const id = route.params.id;
        try {
            const [pRes, tRes] = await Promise.all([
                apiFetch<{ data: ProjectDetail }>(`/v1/projects/${id}`),
                apiFetch<{ data: { phases: TimelinePhase[] } }>(`/v1/projects/${id}/timeline`),
            ]);
            project.value = pRes.data ?? null;
            timelinePhases.value = tRes.data?.phases ?? [];
            if (project.value) {
                await loadTeam(id);
            }
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
                        $t('projects.team_title')
                    }}</span>
                </template>
                <div v-if="teamLoadError" class="text-sm text-[#666666]">
                    {{ $t('projects.team_load_error') }}
                </div>
                <div v-else-if="isTeamLoading" class="text-sm text-[#666666]">
                    {{ $t('shell.loading') }}
                </div>
                <div v-else class="space-y-4">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-[#666666]">
                            {{ $t('projects.team_members') }}
                        </p>
                        <p v-if="teamMembers.length === 0" class="mt-1 text-sm text-[#4d4d4d]">
                            {{ $t('projects.team_empty_members') }}
                        </p>
                        <ul v-else class="mt-2 space-y-2 text-sm text-[#4d4d4d]">
                            <li
                                v-for="m in teamMembers"
                                :key="m.id"
                                class="flex flex-wrap items-center justify-between gap-2"
                            >
                                <span>{{ m.user.name }} — {{ m.user.email }}</span>
                                <UBadge color="gray" variant="soft">{{
                                    m.project_role_label
                                }}</UBadge>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-[#666666]">
                            {{ $t('projects.team_invitations') }}
                        </p>
                        <p v-if="teamInvitations.length === 0" class="mt-1 text-sm text-[#4d4d4d]">
                            {{ $t('projects.team_empty_invites') }}
                        </p>
                        <ul v-else class="mt-2 space-y-1 text-sm text-[#4d4d4d]">
                            <li v-for="inv in teamInvitations" :key="inv.id">
                                {{ inv.email }} — {{ inv.project_role_label }}
                            </li>
                        </ul>
                    </div>
                    <div
                        v-if="canManageTeam"
                        class="space-y-2 border-t border-[#ebebeb] pt-4 dark:border-neutral-800"
                    >
                        <UFormField :label="$t('projects.team_email')" name="invite_email">
                            <UInput v-model="inviteEmail" type="email" autocomplete="email" />
                        </UFormField>
                        <UFormField :label="$t('projects.team_role')" name="invite_role">
                            <USelect
                                v-model="inviteRole"
                                :options="inviteRoleOptions"
                                option-attribute="label"
                                value-attribute="value"
                            />
                        </UFormField>
                        <UButton
                            :loading="isInviteSubmitting"
                            class="font-medium"
                            @click="submitInvite(route.params.id)"
                        >
                            {{ $t('projects.team_invite') }}
                        </UButton>
                    </div>
                </div>
            </UCard>

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
