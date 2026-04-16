<script setup lang="ts">
    import type { UserRole } from '~/types/auth';
    import AdminAssignRoleModal from '~/components/admin/AssignRoleModal.vue';

    definePageMeta({
        // Use the same shell layout as the rest of the app (sidebar + breadcrumbs)
        // so Admin pages feel consistent with project pages.
        layout: 'default',
        middleware: ['auth', 'role'],
        roles: ['admin'],
    });

    interface AdminUser {
        id: number;
        name: string;
        email: string;
        role: UserRole;
        role_label: string;
        phone: string | null;
        active: boolean;
        email_verified_at: string | null;
        created_at: string;
    }

    const { apiFetch } = useApi();
    const toast = useToast();

    const roleFilter = ref<string>('');
    const page = ref(1);
    const perPage = ref(15);
    const isLoading = ref(false);
    const loadError = ref<string | null>(null);
    const users = ref<AdminUser[]>([]);
    const totalUsers = ref(0);
    const totalPages = ref(1);

    const assignModalOpen = ref(false);
    const selectedUser = ref<AdminUser | null>(null);

    const roleOptions = [
        { label: 'الكل', value: '' },
        { label: 'العميل', value: 'customer' },
        { label: 'المقاول', value: 'contractor' },
        { label: 'المهندس المشرف', value: 'supervising_architect' },
        { label: 'المهندس الميداني', value: 'field_engineer' },
        { label: 'الإدارة', value: 'admin' },
    ];

    const columns = [
        { key: 'name', label: 'الاسم' },
        { key: 'email', label: 'البريد الإلكتروني' },
        { key: 'role_label', label: 'الدور' },
        { key: 'active', label: 'الحالة' },
        { key: 'created_at', label: 'تاريخ الانضمام' },
        { key: 'actions', label: 'الإجراءات' },
    ];

    async function fetchUsers() {
        isLoading.value = true;
        loadError.value = null;
        try {
            const params = new URLSearchParams({
                page: page.value.toString(),
                per_page: perPage.value.toString(),
            });
            if (roleFilter.value) {
                params.set('role', roleFilter.value);
            }

            const response = await apiFetch<{
                success: boolean;
                data: {
                    data: AdminUser[];
                    meta: { total: number; last_page: number };
                };
            }>(`/v1/admin/users?${params.toString()}`);

            users.value = response.data.data;
            totalUsers.value = response.data.meta.total;
            totalPages.value = response.data.meta.last_page;
        } catch (e: unknown) {
            users.value = [];
            totalUsers.value = 0;
            totalPages.value = 1;
            loadError.value = e instanceof Error ? e.message : String(e);
            toast.add({
                title: 'خطأ',
                description: 'فشل في تحميل قائمة المستخدمين',
                color: 'red',
            });
        } finally {
            isLoading.value = false;
        }
    }

    function openAssignModal(user: AdminUser) {
        selectedUser.value = user;
        assignModalOpen.value = true;
    }

    function onRoleAssigned(updated: AdminUser) {
        assignModalOpen.value = false;
        selectedUser.value = null;

        // Optimistic update (so user sees it immediately), then refresh from server.
        const idx = users.value.findIndex((u) => u.id === updated.id);
        if (idx !== -1) {
            users.value[idx] = { ...(users.value[idx] as AdminUser), ...updated };
        }

        void fetchUsers();
    }

    watch([roleFilter, page], () => fetchUsers());

    onMounted(() => fetchUsers());
</script>

<template>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717]"
                style="letter-spacing: -0.06em"
            >
                إدارة المستخدمين
            </h1>
            <USelect
                v-model="roleFilter"
                :options="roleOptions"
                option-attribute="label"
                value-attribute="value"
                placeholder="تصفية حسب الدور"
                class="w-48"
            />
        </div>

        <UAlert
            v-if="loadError"
            color="red"
            variant="soft"
            :title="$t('shell.error')"
            :description="loadError"
            class="shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]"
        />

        <p v-else-if="!isLoading && users.length === 0" class="text-sm text-[#666666]">
            {{ $t('shell.empty') }}
        </p>

        <UTable v-else :rows="users" :columns="columns" :loading="isLoading">
            <template #active-data="{ row }">
                <UBadge :color="row.active ? 'green' : 'red'" variant="subtle">
                    {{ row.active ? 'نشط' : 'غير نشط' }}
                </UBadge>
            </template>

            <template #created_at-data="{ row }">
                {{ new Date(row.created_at).toLocaleDateString('ar-SA') }}
            </template>

            <template #actions-data="{ row }">
                <UButton
                    size="xs"
                    color="gray"
                    variant="ghost"
                    icon="i-heroicons-pencil-square"
                    @click.stop.prevent="openAssignModal(row)"
                >
                    تعيين دور
                </UButton>
            </template>
        </UTable>

        <div v-if="totalPages > 1" class="flex justify-center">
            <UPagination v-model="page" :total="totalUsers" :page-count="perPage" />
        </div>

        <AdminAssignRoleModal
            v-model:open="assignModalOpen"
            :user="selectedUser"
            @assigned="onRoleAssigned"
        />
    </div>
</template>
