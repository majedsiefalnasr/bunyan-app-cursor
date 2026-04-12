<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const route = useRoute();
    const localePath = useLocalePath();
    const { t } = useI18n();
    const { apiFetch } = useApi();

    const project = ref<ProjectShellDetail | null>(null);
    provide(PROJECT_SHELL_KEY, project);

    const projectId = computed(() => String(route.params.id));
    const isLoading = ref(true);
    const loadError = ref(false);

    const overviewPath = computed(() => localePath(`/projects/${projectId.value}`));

    const navItems = computed(() => {
        const id = projectId.value;
        return [
            { to: localePath(`/projects/${id}`), label: t('projects.nav_overview') },
            { to: localePath(`/projects/${id}/tasks`), label: t('projects.nav_tasks') },
            { to: localePath(`/projects/${id}/documents`), label: t('projects.nav_documents') },
            { to: localePath(`/projects/${id}/team`), label: t('projects.nav_team') },
            { to: localePath(`/projects/${id}/workflow`), label: t('projects.nav_workflow') },
            { to: localePath(`/projects/${id}/estimates`), label: t('projects.nav_estimates') },
        ];
    });

    function isNavActive(to: string): boolean {
        const path = route.path;
        if (to === overviewPath.value) {
            return path === to;
        }
        return path === to || path.startsWith(`${to}/`);
    }

    onMounted(async () => {
        isLoading.value = true;
        loadError.value = false;
        try {
            const res = await apiFetch<{ data: ProjectShellDetail }>(
                `/v1/projects/${projectId.value}`
            );
            project.value = res.data ?? null;
        } catch {
            loadError.value = true;
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-5xl space-y-6">
        <UButton :to="localePath('/projects')" variant="soft" color="gray">
            {{ $t('projects.back_to_list') }}
        </UButton>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <UAlert
            v-else-if="loadError || !project"
            color="red"
            variant="soft"
            :title="$t('projects.shell_load_error')"
        />

        <template v-else>
            <div class="space-y-2">
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.06em"
                >
                    {{ project.name }}
                </h1>
                <p class="text-sm text-[#4d4d4d]">
                    {{ $t('projects.status_label') }}:
                    <UBadge
                        class="ms-1"
                        :color="projectStatusBadgeColor(project.status)"
                        variant="soft"
                    >
                        {{ project.status }}
                    </UBadge>
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <UButton
                    v-for="item in navItems"
                    :key="item.to"
                    :to="item.to"
                    size="sm"
                    :variant="isNavActive(item.to) ? 'solid' : 'soft'"
                    :color="isNavActive(item.to) ? 'primary' : 'gray'"
                >
                    {{ item.label }}
                </UButton>
            </div>

            <NuxtPage />
        </template>
    </div>
</template>
