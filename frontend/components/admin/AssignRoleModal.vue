<script setup lang="ts">
    import type { UserRole } from '~/types/auth';

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

    const props = defineProps<{
        user: AdminUser | null;
    }>();

    const open = defineModel<boolean>('open', { default: false });

    const emit = defineEmits<{
        assigned: [updated: AdminUser];
    }>();

    const { apiFetch } = useApi();
    const toast = useToast();

    const selectedRole = ref<string>('');
    const isSubmitting = ref(false);

    const roleOptions = [
        { label: 'العميل', value: 'customer' },
        { label: 'المقاول', value: 'contractor' },
        { label: 'المهندس المشرف', value: 'supervising_architect' },
        { label: 'المهندس الميداني', value: 'field_engineer' },
        { label: 'الإدارة', value: 'admin' },
    ];

    watch(
        () => props.user,
        (u) => {
            if (u) selectedRole.value = u.role;
        }
    );

    async function assignRole() {
        if (!props.user || !selectedRole.value) return;

        isSubmitting.value = true;
        try {
            const res = await apiFetch<{ data: AdminUser }>(
                `/v1/admin/users/${props.user.id}/role`,
                {
                    method: 'POST',
                    body: { role: selectedRole.value },
                }
            );

            toast.add({
                title: 'تم بنجاح',
                description: 'تم تعيين الدور بنجاح',
                color: 'success',
                icon: 'i-heroicons-check-circle',
            });

            const updated = res?.data ?? { ...props.user, role: selectedRole.value as UserRole };
            emit('assigned', updated);
            open.value = false;
        } catch (error: unknown) {
            const o = error as { response?: { _data?: unknown } };
            const data = o?.response?._data as unknown;
            const message =
                data &&
                typeof data === 'object' &&
                'error' in (data as Record<string, unknown>) &&
                (data as { error?: unknown }).error &&
                typeof (data as { error?: { message?: unknown } }).error?.message === 'string'
                    ? String((data as { error: { message: string } }).error.message)
                    : 'فشل في تعيين الدور';
            toast.add({
                title: 'خطأ',
                description: message,
                color: 'error',
                icon: 'i-heroicons-exclamation-triangle',
            });
        } finally {
            isSubmitting.value = false;
        }
    }
</script>

<template>
    <UModal v-model="open">
        <UCard>
            <template #header>
                <h3 class="text-lg font-semibold" style="color: #171717">تعيين دور</h3>
                <p v-if="user" class="mt-1 text-sm" style="color: #666">
                    {{ user.name }} — {{ user.role_label }}
                </p>
            </template>

            <div class="space-y-4">
                <UFormGroup label="الدور الجديد">
                    <USelect
                        v-model="selectedRole"
                        :options="roleOptions"
                        option-attribute="label"
                        value-attribute="value"
                    />
                </UFormGroup>
            </div>

            <template #footer>
                <div class="flex justify-end gap-3">
                    <UButton color="neutral" variant="ghost" @click="open = false"> إلغاء </UButton>
                    <UButton :loading="isSubmitting" @click="assignRole"> تأكيد التعيين </UButton>
                </div>
            </template>
        </UCard>
    </UModal>
</template>
