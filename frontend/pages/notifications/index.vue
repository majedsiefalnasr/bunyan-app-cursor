<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const { apiFetch } = useApi();
    const { t, locale } = useI18n();
    const localePath = useLocalePath();

    interface NotificationRow {
        id: string;
        title_ar: string;
        title_en: string;
        body_ar: string;
        body_en: string;
        read_at: string | null;
        created_at: string | null;
    }

    const items = ref<NotificationRow[]>([]);
    const loading = ref(true);

    const titleFor = (n: NotificationRow) =>
        (locale.value === 'ar' ? n.title_ar : n.title_en) || n.title_en || n.title_ar;
    const bodyFor = (n: NotificationRow) =>
        (locale.value === 'ar' ? n.body_ar : n.body_en) || n.body_en || n.body_ar;

    async function load() {
        loading.value = true;
        try {
            const res = await apiFetch<{ data: NotificationRow[] }>(
                '/v1/notifications?per_page=50'
            );
            items.value = Array.isArray(res.data) ? res.data : [];
        } finally {
            loading.value = false;
        }
    }

    async function markRead(id: string) {
        await apiFetch(`/v1/notifications/${id}/read`, { method: 'PUT' });
        await load();
    }

    async function markAll() {
        await apiFetch('/v1/notifications/read-all', { method: 'PUT' });
        await load();
    }

    onMounted(() => {
        void load();
    });
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                {{ t('notifications.title') }}
            </h1>
            <div class="flex gap-2">
                <UButton
                    color="neutral"
                    variant="soft"
                    size="sm"
                    :to="localePath('/notifications/settings')"
                >
                    {{ t('notifications.settings') }}
                </UButton>
                <UButton color="primary" size="sm" :disabled="loading" @click="markAll">
                    {{ t('notifications.markAll') }}
                </UButton>
            </div>
        </div>

        <div v-if="loading" class="text-sm text-[#666666]">{{ t('shell.loading') }}</div>

        <div
            v-else-if="items.length === 0"
            class="rounded-lg bg-white p-8 text-center text-[#666666] shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:bg-[#171717] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
        >
            {{ t('notifications.empty') }}
        </div>

        <ul v-else class="space-y-3">
            <li
                v-for="n in items"
                :key="n.id"
                class="rounded-lg bg-white p-4 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:bg-[#171717] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="font-medium text-[#171717] dark:text-white">{{ titleFor(n) }}</p>
                        <p class="mt-1 text-sm text-[#666666]">{{ bodyFor(n) }}</p>
                        <p v-if="!n.read_at" class="mt-2 text-xs font-medium text-[#0a72ef]">
                            {{ t('notifications.unread') }}
                        </p>
                    </div>
                    <UButton
                        v-if="!n.read_at"
                        size="xs"
                        color="neutral"
                        variant="soft"
                        @click="markRead(n.id)"
                    >
                        {{ t('notifications.markRead') }}
                    </UButton>
                </div>
            </li>
        </ul>
    </div>
</template>
