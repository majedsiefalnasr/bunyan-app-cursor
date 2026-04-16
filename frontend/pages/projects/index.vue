<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
        requiresAuth: true,
    });

    const localePath = useLocalePath();
    const { apiFetch } = useApi();

    interface ProjectRow {
        id: number;
        name: string;
        status: string;
        start_date: string | null;
        end_date: string | null;
    }

    const projects = ref<ProjectRow[]>([]);
    const isLoading = ref(true);
    const loadError = ref<string | null>(null);

    onMounted(async () => {
        loadError.value = null;
        try {
            const res = await apiFetch<{ data: { data?: ProjectRow[] } | ProjectRow[] }>(
                '/v1/projects'
            );
            const payload = res.data as { data?: ProjectRow[] } | ProjectRow[];
            projects.value = Array.isArray(payload) ? payload : (payload.data ?? []);
        } catch (e: unknown) {
            projects.value = [];
            loadError.value = e instanceof Error ? e.message : String(e);
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                    {{ $t('projects.list_title') }}
                </h1>
                <p class="mt-1 text-sm text-[#666666]">
                    {{ $t('projects.list_subtitle') }}
                </p>
            </div>
            <UButton :to="localePath('/projects/create')" color="primary" variant="solid" size="sm">
                {{ $t('projects.new_title') }}
            </UButton>
        </div>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <UAlert
            v-else-if="loadError"
            color="error"
            variant="soft"
            :title="$t('shell.error')"
            :description="loadError"
        />

        <p v-else-if="projects.length === 0" class="text-sm text-[#666666]">
            {{ $t('projects.empty') }}
        </p>

        <UPageGrid v-else class="gap-4 sm:gap-6 lg:grid-cols-3">
            <UPageCard
                v-for="p in projects"
                :key="p.id"
                variant="subtle"
                :title="p.name"
                :ui="{
                    container: 'gap-y-1.5',
                    title: 'font-medium text-[#171717] dark:text-white',
                }"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08),0px_2px_2px_rgba(0,0,0,0.04)]"
            >
                <div class="flex items-center justify-between gap-2">
                    <UBadge
                        :color="projectStatusBadgeColor(p.status) as any"
                        variant="soft"
                        size="sm"
                    >
                        {{ p.status }}
                    </UBadge>
                    <UButton
                        :to="localePath(`/projects/${p.id}`)"
                        variant="soft"
                        color="neutral"
                        size="xs"
                    >
                        {{ $t('projects.detail_title') }}
                    </UButton>
                </div>
            </UPageCard>
        </UPageGrid>
    </div>
</template>
