<script setup lang="ts">
    import type { UserRole } from '~/types/auth';

    definePageMeta({
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
        } catch {
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

    function onRoleAssigned() {
        assignModalOpen.value = false;
        fetchUsers();
    }

    watch([roleFilter, page], () => fetchUsers());

    onMounted(() => fetchUsers());
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold tracking-tight" style="color: #171717">
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

        <UTable :rows="users" :columns="columns" :loading="isLoading">
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
                    @click="openAssignModal(row)"
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
