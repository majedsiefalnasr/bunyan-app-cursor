<script setup lang="ts">
    definePageMeta({
        layout: 'default',
        middleware: 'auth',
    });

    const { apiFetch } = useApi();
    const { t } = useI18n();
    const localePath = useLocalePath();

    interface PrefRow {
        type: string;
        email_enabled: boolean;
        sms_enabled: boolean;
        push_enabled: boolean;
    }

    const rows = ref<PrefRow[]>([]);
    const loading = ref(true);
    const saving = ref(false);
    const toast = useToast();

    async function load() {
        loading.value = true;
        try {
            const res = await apiFetch<{ data: PrefRow[] }>('/v1/notification-preferences');
            rows.value = Array.isArray(res.data) ? res.data.map((r) => ({ ...r })) : [];
        } finally {
            loading.value = false;
        }
    }

    async function save() {
        saving.value = true;
        try {
            await apiFetch('/v1/notification-preferences', {
                method: 'PUT',
                body: { preferences: rows.value },
            });
            toast.add({ title: t('notifications.saved'), color: 'success' });
            await load();
        } finally {
            saving.value = false;
        }
    }

    onMounted(() => {
        void load();
    });
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold tracking-tight text-[#171717] dark:text-white">
                {{ t('notifications.settings') }}
            </h1>
            <UButton color="neutral" variant="soft" size="sm" :to="localePath('/notifications')">
                {{ t('notifications.history') }}
            </UButton>
        </div>

        <div v-if="loading" class="text-sm text-[#666666]">{{ t('shell.loading') }}</div>

        <UCard
            v-else
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)]"
        >
            <div class="space-y-6">
                <div
                    v-for="row in rows"
                    :key="row.type"
                    class="space-y-3 border-b border-[#ebebeb] pb-6 last:border-0 last:pb-0 dark:border-white/10"
                >
                    <p class="text-sm font-medium text-[#171717] dark:text-white">
                        {{ row.type }}
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <UCheckbox v-model="row.email_enabled" :label="t('notifications.email')" />
                        <UCheckbox v-model="row.sms_enabled" :label="t('notifications.sms')" />
                        <UCheckbox v-model="row.push_enabled" :label="t('notifications.push')" />
                    </div>
                </div>
            </div>

            <template #footer>
                <UButton :loading="saving" color="primary" @click="save">
                    {{ t('notifications.save') }}
                </UButton>
            </template>
        </UCard>
    </div>
</template>
