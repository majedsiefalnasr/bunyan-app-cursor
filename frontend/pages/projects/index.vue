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

    onMounted(async () => {
        try {
            const res = await apiFetch<{ data: { data?: ProjectRow[] } | ProjectRow[] }>(
                '/v1/projects'
            );
            const payload = res.data as { data?: ProjectRow[] } | ProjectRow[];
            projects.value = Array.isArray(payload) ? payload : (payload.data ?? []);
        } finally {
            isLoading.value = false;
        }
    });
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white"
                    style="letter-spacing: -0.06em"
                >
                    {{ $t('projects.list_title') }}
                </h1>
                <p class="mt-1 text-sm text-[#666666]">
                    {{ $t('projects.list_subtitle') }}
                </p>
            </div>
            <UButton :to="localePath('/projects/create')" color="primary" variant="solid">
                {{ $t('projects.new_title') }}
            </UButton>
        </div>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <p v-else-if="projects.length === 0" class="text-sm text-[#666666]">
            {{ $t('projects.empty') }}
        </p>

        <div v-else class="grid gap-4 sm:grid-cols-2">
            <UCard
                v-for="p in projects"
                :key="p.id"
                class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
            >
                <div class="space-y-2">
                    <h2 class="text-lg font-semibold text-[#171717] dark:text-white">
                        {{ p.name }}
                    </h2>
                    <UBadge :color="projectStatusBadgeColor(p.status)" variant="soft">{{
                        p.status
                    }}</UBadge>
                    <UButton :to="localePath(`/projects/${p.id}`)" variant="soft" color="gray">
                        {{ $t('projects.detail_title') }}
                    </UButton>
                </div>
            </UCard>
        </div>
    </div>
</template>
