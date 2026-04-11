<script setup lang="ts">
    import { navigationItems } from '~/config/navigation';
    import type { UserRole } from '~/types/auth';

    const uiStore = useUIStore();
    const isOpen = computed({
        get: () => uiStore.isSidebarOpen,
        set: (val) => uiStore.setSidebarOpen(val),
    });

    const { role } = useAuth();
    const { t } = useI18n();
    const localePath = useLocalePath();

    const navLinks = computed(() => {
        const userRole = role.value as UserRole | null;
        return navigationItems
            .filter((item) => item.roles.length === 0 || (userRole && item.roles.includes(userRole)))
            .map((item) => ({
                label: t(item.labelKey),
                icon: item.icon,
                to: localePath(item.to),
                click: () => uiStore.setSidebarOpen(false),
            }));
    });
</script>

<template>
    <USlideOver
        v-model="isOpen"
        side="left"
        :ui="{ width: 'w-64' }"
    >
        <div class="flex h-full flex-col bg-white dark:bg-[#171717]">
            <div
                class="flex items-center justify-between p-4 shadow-[0px_1px_0px_0px_rgba(0,0,0,0.08)] dark:shadow-[0px_1px_0px_0px_rgba(255,255,255,0.06)]"
            >
                <span class="text-base font-semibold text-[#171717] dark:text-white">
                    {{ $t('app.name') }}
                </span>
                <UButton
                    color="gray"
                    variant="ghost"
                    icon="i-heroicons-x-mark"
                    :aria-label="$t('shell.sidebar.close')"
                    @click="uiStore.setSidebarOpen(false)"
                />
            </div>
            <nav :aria-label="$t('shell.nav.main')" class="flex-1 overflow-y-auto p-4">
                <UVerticalNavigation :links="navLinks" />
            </nav>
        </div>
    </USlideOver>
</template>
