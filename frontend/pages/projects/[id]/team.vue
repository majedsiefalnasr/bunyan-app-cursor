<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const route = useRoute();
    const { t } = useI18n();
    const toast = useToast();
    const { apiFetch } = useApi();
    const { hasRole } = useAuth();

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

    const projectId = computed(() => String(route.params.id));
    const teamMembers = ref<TeamMemberRow[]>([]);
    const teamInvitations = ref<TeamInvitationRow[]>([]);
    const teamLoadError = ref(false);
    const inviteEmail = ref('');
    const inviteRole = ref<'manager' | 'engineer' | 'worker' | 'viewer'>('engineer');
    const isTeamLoading = ref(false);
    const isInviteSubmitting = ref(false);

    const canManageTeam = computed(() =>
        hasRole('customer', 'contractor', 'supervising_architect', 'admin')
    );

    const inviteRoleOptions = computed(() => [
        { value: 'manager', label: t('projects.team_role_manager') },
        { value: 'engineer', label: t('projects.team_role_engineer') },
        { value: 'worker', label: t('projects.team_role_worker') },
        { value: 'viewer', label: t('projects.team_role_viewer') },
    ]);

    async function loadTeam() {
        isTeamLoading.value = true;
        teamLoadError.value = false;
        try {
            const res = await apiFetch<{
                data: { members: TeamMemberRow[]; invitations_pending: TeamInvitationRow[] };
            }>(`/v1/projects/${projectId.value}/team`);
            teamMembers.value = res.data?.members ?? [];
            teamInvitations.value = res.data?.invitations_pending ?? [];
        } catch {
            teamLoadError.value = true;
        } finally {
            isTeamLoading.value = false;
        }
    }

    async function submitInvite() {
        if (!inviteEmail.value.trim()) {
            return;
        }
        isInviteSubmitting.value = true;
        try {
            await apiFetch(`/v1/projects/${projectId.value}/team`, {
                method: 'POST',
                body: {
                    email: inviteEmail.value.trim().toLowerCase(),
                    project_role: inviteRole.value,
                },
            });
            inviteEmail.value = '';
            toast.add({ title: t('projects.team_invite_success'), color: 'success' });
            await loadTeam();
        } finally {
            isInviteSubmitting.value = false;
        }
    }

    onMounted(loadTeam);
</script>

<template>
    <div class="space-y-6">
        <div>
            <h2
                class="text-xl font-semibold tracking-tight text-[#171717] dark:text-white"
                style="letter-spacing: -0.04em"
            >
                {{ $t('projects.team_title') }}
            </h2>
            <p class="mt-2 text-sm text-[#4d4d4d]">
                {{ $t('projects.team_subtitle') }}
            </p>
        </div>

        <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
            <template #header>
                <span class="text-sm font-medium text-[#171717] dark:text-white">{{
                    $t('projects.team_title')
                }}</span>
            </template>
            <div v-if="teamLoadError" class="text-sm text-[#666666]">
                {{ $t('projects.team_load_error') }}
            </div>
            <div v-else-if="isTeamLoading" class="text-sm text-[#666666]">
                {{ $t('shell.loading') }}
            </div>
            <div v-else class="space-y-8">
                <div>
                    <p class="text-xs font-medium uppercase tracking-wide text-[#666666]">
                        {{ $t('projects.team_members') }}
                    </p>
                    <p v-if="teamMembers.length === 0" class="mt-1 text-sm text-[#4d4d4d]">
                        {{ $t('projects.team_empty_members') }}
                    </p>
                    <ul v-else class="mt-3 space-y-2 text-sm text-[#4d4d4d]">
                        <li
                            v-for="m in teamMembers"
                            :key="m.id"
                            class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-default bg-elevated/20 px-3 py-2 dark:bg-elevated/10"
                        >
                            <span class="min-w-0"
                                >{{ m.user.name }}
                                <span class="text-[#666666]">— {{ m.user.email }}</span></span
                            >
                            <UBadge color="neutral" variant="soft" class="shrink-0">{{
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
                    <ul v-else class="mt-3 space-y-2 text-sm text-[#4d4d4d]">
                        <li
                            v-for="inv in teamInvitations"
                            :key="inv.id"
                            class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-default px-3 py-2"
                        >
                            <span>{{ inv.email }}</span>
                            <UBadge color="neutral" variant="soft">{{ inv.project_role_label }}</UBadge>
                        </li>
                    </ul>
                </div>
                <div
                    v-if="canManageTeam"
                    class="rounded-xl border border-default bg-elevated/30 p-4 sm:p-5 dark:bg-elevated/15"
                >
                    <div>
                        <p class="text-sm font-medium text-[#171717] dark:text-white">
                            {{ $t('projects.team_invite_heading') }}
                        </p>
                        <p class="mt-1 text-xs text-[#666666]">
                            {{ $t('projects.team_invite_description') }}
                        </p>
                    </div>
                    <div
                        class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-12 sm:items-end"
                        role="group"
                        :aria-label="$t('projects.team_invite_heading')"
                    >
                        <UFormGroup
                            class="min-w-0 w-full sm:col-span-6"
                            :label="$t('projects.team_email')"
                            name="invite_email"
                        >
                            <UInput
                                v-model="inviteEmail"
                                type="email"
                                autocomplete="email"
                                class="w-full min-w-0"
                            />
                        </UFormGroup>
                        <UFormGroup
                            class="min-w-0 w-full sm:col-span-3"
                            :label="$t('projects.team_role')"
                            name="invite_role"
                        >
                            <USelect
                                v-model="inviteRole"
                                :items="inviteRoleOptions"
                                class="w-full min-w-0"
                            />
                        </UFormGroup>
                        <UFormGroup
                            class="min-w-0 w-full sm:col-span-3"
                            :label="$t('projects.team_action_label')"
                        >
                            <UButton
                                class="w-full justify-center font-medium"
                                :loading="isInviteSubmitting"
                                @click="submitInvite"
                            >
                                {{ $t('projects.team_invite') }}
                            </UButton>
                        </UFormGroup>
                    </div>
                </div>
            </div>
        </UCard>
    </div>
</template>
