<script setup lang="ts">
    const props = defineProps<{
        entity: string;
        subjectId: number;
    }>();

    const { t } = useI18n();
    const { apiFetch } = useApi();
    const toast = useToast();

    interface ActivityRow {
        id: number;
        action: string;
        subject_type: string;
        subject_id: number;
        created_at?: string;
        actor?: { id: number; name: string } | null;
    }

    const items = ref<ActivityRow[]>([]);
    const isLoading = ref(true);

    function extractRows(res: unknown): ActivityRow[] {
        const root = res as { data?: unknown };
        const d = root.data;
        if (
            d &&
            typeof d === 'object' &&
            'data' in d &&
            Array.isArray((d as { data: ActivityRow[] }).data)
        ) {
            return (d as { data: ActivityRow[] }).data;
        }
        if (Array.isArray(d)) {
            return d as ActivityRow[];
        }
        return [];
    }

    async function load() {
        isLoading.value = true;
        try {
            const res = await apiFetch<unknown>(
                `/v1/${props.entity}/${props.subjectId}/activity?per_page=20`
            );
            items.value = extractRows(res);
        } catch {
            toast.add({
                title: t('errors.codes.SERVER_ERROR.message'),
                description: t('activityLog.load_error'),
                color: 'error',
            });
        } finally {
            isLoading.value = false;
        }
    }

    watch(
        () => [props.entity, props.subjectId] as const,
        () => {
            void load();
        },
        { immediate: true }
    );
</script>

<template>
    <UCard class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
        <template #header>
            <span class="font-medium text-[#171717] dark:text-white">{{
                t('activityLog.project_activity_title')
            }}</span>
        </template>
        <div v-if="isLoading" class="text-sm text-muted">{{ $t('shell.loading') }}</div>
        <p v-else-if="items.length === 0" class="text-sm text-muted">
            {{ t('activityLog.empty') }}
        </p>
        <ul v-else class="space-y-3 text-sm">
            <li
                v-for="row in items"
                :key="row.id"
                class="rounded-lg border border-default bg-elevated/30 px-3 py-2 dark:bg-elevated/15"
            >
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-medium text-[#171717] dark:text-white">{{ row.action }}</span>
                    <span v-if="row.created_at" class="text-xs text-muted">{{
                        new Date(row.created_at).toLocaleString()
                    }}</span>
                </div>
                <p v-if="row.actor" class="mt-1 text-xs text-muted">
                    {{ t('activityLog.actor') }}: {{ row.actor.name }}
                </p>
            </li>
        </ul>
    </UCard>
</template>
