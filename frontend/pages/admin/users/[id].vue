<script setup lang="ts">
    definePageMeta({
        layout: 'admin',
        middleware: ['auth', 'role'],
        roles: ['admin'],
    });

    const { t } = useI18n();
    const route = useRoute();
    const toast = useToast();

    type UserRole = import('~/types/auth').UserRole;

    interface AdminUserDetail {
        id: number;
        name: string;
        email: string;
        role: UserRole;
        role_label?: string;
        phone?: string | null;
        active?: boolean;
        created_at?: string;
    }

    const userId = computed(() => Number(route.params.id));
    const isLoading = ref(false);
    const user = ref<AdminUserDetail | null>(null);

    async function load() {
        if (!Number.isFinite(userId.value) || userId.value <= 0) return;

        // Backend does not currently expose a dedicated "admin user show" endpoint.
        // Keep the page as a scaffold until the API contract is available.
        user.value = { id: userId.value, name: '', email: '', role: 'admin' as UserRole };
        toast.add({
            title: t('admin.user_detail.api_missing_title'),
            description: t('admin.user_detail.api_missing_note'),
            color: 'amber',
        });
    }

    onMounted(() => {
        void load();
    });
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3 rtl:flex-row-reverse">
            <div>
                <h1
                    class="text-2xl font-semibold tracking-tight text-[#171717]"
                    style="letter-spacing: -0.06em"
                >
                    {{ t('admin.user_detail.title') }}
                </h1>
                <p v-if="user" class="mt-1 text-sm text-[#4d4d4d]">
                    {{ user.name }} — {{ user.email }}
                </p>
            </div>
            <UButton color="gray" variant="soft" to="/admin/users" icon="i-heroicons-arrow-right">
                {{ t('admin.user_detail.back_to_users') }}
            </UButton>
        </div>

        <UCard>
            <div v-if="isLoading" class="text-sm text-[#666666]">
                {{ t('shell.loading') }}
            </div>
            <div v-else-if="!user" class="text-sm text-[#666666]">
                {{ t('admin.user_detail.not_found') }}
            </div>
            <div v-else class="grid grid-cols-1 gap-3 md:grid-cols-2">
                <div>
                    <div class="text-xs text-[#808080]">ID</div>
                    <div class="text-sm text-[#171717]">{{ user.id }}</div>
                </div>
                <div>
                    <div class="text-xs text-[#808080]">
                        {{ t('admin.user_detail.fields.name') }}
                    </div>
                    <div class="text-sm text-[#171717]">{{ user.name }}</div>
                </div>
                <div>
                    <div class="text-xs text-[#808080]">
                        {{ t('admin.user_detail.fields.email') }}
                    </div>
                    <div class="text-sm text-[#171717]">{{ user.email }}</div>
                </div>
                <div>
                    <div class="text-xs text-[#808080]">
                        {{ t('admin.user_detail.fields.role') }}
                    </div>
                    <div class="text-sm text-[#171717]">{{ user.role_label || user.role }}</div>
                </div>
                <div>
                    <div class="text-xs text-[#808080]">
                        {{ t('admin.user_detail.fields.phone') }}
                    </div>
                    <div class="text-sm text-[#171717]">{{ user.phone || '—' }}</div>
                </div>
            </div>
        </UCard>
    </div>
</template>
