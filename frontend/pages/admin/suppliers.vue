<script setup lang="ts">
    definePageMeta({
        layout: 'admin',
        middleware: ['auth', 'role'],
        requiresAuth: true,
        roles: ['admin'],
    });

    const { t } = useI18n();
    const { apiFetch } = useApi();
    const toast = useToast();

    interface Row {
        id: number;
        company_name_ar: string;
        verification_status: string;
        user?: { email?: string; name?: string };
    }

    const rows = ref<Row[]>([]);
    const isLoading = ref(true);

    async function load() {
        isLoading.value = true;
        try {
            const res = await apiFetch<{ data: Row[] }>('/v1/admin/suppliers');
            rows.value = res.data ?? [];
        } finally {
            isLoading.value = false;
        }
    }

    async function setStatus(id: number, status: 'verified' | 'suspended') {
        try {
            await apiFetch(`/v1/suppliers/${id}/verify`, {
                method: 'PUT',
                body: { verification_status: status },
            });
            toast.add({ title: 'تم', description: 'تم تحديث الحالة', color: 'success' });
            await load();
        } catch {
            /* useApi surfaces toast */
        }
    }

    const columns = computed(() => [
        { key: 'company_name_ar', label: t('suppliers.company_name_ar') },
        { key: 'verification_status', label: t('suppliers.verification_status') },
        { key: 'actions', label: '' },
    ]);

    onMounted(load);
</script>

<template>
    <div class="mx-auto max-w-5xl space-y-6">
        <h1 class="text-2xl font-semibold text-[#171717] dark:text-white">
            {{ $t('suppliers.admin_title') }}
        </h1>

        <div v-if="isLoading" class="text-sm text-[#666666]">
            {{ $t('shell.loading') }}
        </div>

        <UTable v-else :rows="rows as any" :columns="columns as any">
            <template #verification_status-data="{ row }">
                <span class="text-sm">{{ (row as any).verification_status }}</span>
            </template>
            <template #actions-data="{ row }">
                <div class="flex gap-2">
                    <UButton size="xs" @click="setStatus((row as any).id, 'verified')">
                        {{ $t('suppliers.verify') }}
                    </UButton>
                    <UButton
                        size="xs"
                        color="error"
                        variant="soft"
                        @click="setStatus((row as any).id, 'suspended')"
                    >
                        {{ $t('suppliers.suspend') }}
                    </UButton>
                </div>
            </template>
        </UTable>
    </div>
</template>
