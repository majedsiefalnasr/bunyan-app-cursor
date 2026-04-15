<script setup lang="ts">
    definePageMeta({
        layout: 'admin',
        middleware: ['auth', 'role'],
        roles: ['admin'],
    });

    const { t } = useI18n();
    const { apiFetch } = useApi();
    const toast = useToast();

    interface RoleRow {
        id: number;
        name: string;
    }

    interface PermissionRow {
        key: string;
        label?: string | null;
        enabled: boolean;
    }

    const isLoadingRoles = ref(false);
    const roles = ref<RoleRow[]>([]);
    const selectedRoleId = ref<number | undefined>(undefined);

    const isLoadingPermissions = ref(false);
    const permissions = ref<PermissionRow[]>([]);

    async function loadRoles() {
        isLoadingRoles.value = true;
        try {
            const res = await apiFetch<{ success: boolean; data: { roles: RoleRow[] } }>(
                '/v1/admin/roles'
            );
            roles.value = res.data.roles ?? [];
            selectedRoleId.value = roles.value[0]?.id;
        } catch {
            toast.add({
                title: t('errors.codes.SERVER_ERROR.message'),
                description: t('admin.roles.load_error'),
                color: 'red',
            });
        } finally {
            isLoadingRoles.value = false;
        }
    }

    async function loadPermissions(roleId: number) {
        isLoadingPermissions.value = true;
        try {
            const res = await apiFetch<{
                success: boolean;
                data: { permissions: { key: string; label?: string | null; enabled: boolean }[] };
            }>(`/v1/admin/roles/${roleId}/permissions`);
            permissions.value = res.data.permissions ?? [];
        } catch {
            toast.add({
                title: t('errors.codes.SERVER_ERROR.message'),
                description: t('admin.roles.permissions_load_error'),
                color: 'red',
            });
        } finally {
            isLoadingPermissions.value = false;
        }
    }

    watch(selectedRoleId, (id) => {
        if (typeof id === 'number') {
            void loadPermissions(id);
        } else {
            permissions.value = [];
        }
    });

    onMounted(() => {
        void loadRoles();
    });
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717]"
                style="letter-spacing: -0.06em"
            >
                {{ t('admin.roles.title') }}
            </h1>
            <p class="mt-1 text-sm text-[#4d4d4d]">
                {{ t('admin.roles.subtitle') }}
            </p>
        </div>

        <UCard>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="md:col-span-1">
                    <UFormGroup :label="t('admin.roles.role_label')">
                        <USelect
                            v-model="selectedRoleId"
                            :options="roles.map((r) => ({ value: r.id, label: r.name }))"
                            value-attribute="value"
                            option-attribute="label"
                            :loading="isLoadingRoles"
                            :placeholder="t('admin.roles.select_role')"
                        />
                    </UFormGroup>
                </div>

                <div class="md:col-span-2">
                    <RolePermissionMatrix :rows="permissions" :loading="isLoadingPermissions" />
                </div>
            </div>
        </UCard>
    </div>
</template>
