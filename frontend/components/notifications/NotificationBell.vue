<script setup lang="ts">
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

    const unread = ref(0);
    const preview = ref<NotificationRow[]>([]);
    const loading = ref(false);

    const titleFor = (n: NotificationRow) =>
        (locale.value === 'ar' ? n.title_ar : n.title_en) || n.title_en || n.title_ar;

    async function refresh() {
        loading.value = true;
        try {
            const countRes = await apiFetch<{ data: { count: number } }>(
                '/v1/notifications/unread-count'
            );
            unread.value = countRes.data?.count ?? 0;

            const listRes = await apiFetch<{ data: NotificationRow[] }>(
                '/v1/notifications?per_page=5'
            );
            preview.value = Array.isArray(listRes.data) ? listRes.data : [];
        } finally {
            loading.value = false;
        }
    }

    onMounted(() => {
        void refresh();
    });

    const dropdownItems = computed(() => {
        const top = preview.value.map((n) => ({
            label: titleFor(n),
            disabled: true,
            class: 'opacity-100',
        }));

        const actions = [
            {
                label: t('notifications.viewAll'),
                icon: 'i-heroicons-queue-list',
                to: localePath('/notifications'),
                click: async () => {
                    await refresh();
                },
            },
            {
                label: t('notifications.settings'),
                icon: 'i-heroicons-cog-6-tooth',
                to: localePath('/notifications/settings'),
            },
        ];

        if (top.length === 0) {
            return [[{ label: t('notifications.empty'), disabled: true }], actions];
        }

        return [top, actions];
    });
</script>

<template>
    <div class="relative inline-flex">
        <UDropdown :items="dropdownItems">
            <UButton
                as="div"
                color="gray"
                variant="ghost"
                icon="i-heroicons-bell"
                size="sm"
                :aria-label="$t('shell.notifications.open')"
                data-testid="notification-bell"
            />
        </UDropdown>
        <span
            v-if="unread > 0"
            class="pointer-events-none absolute -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#171717] px-1 text-[10px] font-medium text-white end-0 dark:bg-white dark:text-[#171717]"
            :aria-label="$t('shell.notifications.badge')"
        >
            {{ unread > 99 ? '99+' : unread }}
        </span>
    </div>
</template>
