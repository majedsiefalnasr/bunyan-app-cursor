<script setup lang="ts">
    const { user, logout } = useAuth();
    const { t } = useI18n();

    const items = computed(() => [
        [
            {
                label: t('shell.user.profile'),
                icon: 'i-heroicons-user-circle',
                to: '/profile',
            },
        ],
        [
            {
                label: t('shell.user.logout'),
                icon: 'i-heroicons-arrow-right-on-rectangle',
                click: () => logout(),
            },
        ],
    ]);

    const avatarLabel = computed(() => {
        if (!user.value?.name) return '؟';
        return user.value.name.charAt(0).toUpperCase();
    });
</script>

<template>
    <UDropdown :items="items" :ui="{ item: { disabled: 'cursor-text select-text' } }">
        <UButton color="gray" variant="ghost" :aria-label="$t('shell.user.profile')" class="p-1">
            <UAvatar :src="user?.avatar ?? undefined" :alt="user?.name ?? ''" size="sm">
                <template v-if="!user?.avatar" #default>
                    <span class="text-xs font-medium">{{ avatarLabel }}</span>
                </template>
            </UAvatar>
        </UButton>
    </UDropdown>
</template>
