<script setup lang="ts">
    import { navigationItems } from '~/config/navigation';
    import type { UserRole } from '~/types/auth';

    const { role } = useAuth();
    const { t } = useI18n();
    const localePath = useLocalePath();
    const { direction } = useDirection();

    const asideClass = computed(() =>
        direction.value === 'rtl'
            ? 'flex h-full w-64 flex-col bg-white shadow-[-1px_0px_0px_0px_rgba(0,0,0,0.08)] dark:bg-[#171717] dark:shadow-[-1px_0px_0px_0px_rgba(255,255,255,0.06)]'
            : 'flex h-full w-64 flex-col bg-white shadow-[1px_0px_0px_0px_rgba(0,0,0,0.08)] dark:bg-[#171717] dark:shadow-[1px_0px_0px_0px_rgba(255,255,255,0.06)]'
    );

    const navLinks = computed(() => {
        const userRole = role.value as UserRole | null;
        return navigationItems
            .filter(
                (item) => item.roles.length === 0 || (userRole && item.roles.includes(userRole))
            )
            .map((item) => ({
                label: t(item.labelKey),
                icon: item.icon,
                to: localePath(item.to),
                badge: item.badge,
            }));
    });
</script>

<template>
    <aside :class="asideClass">
        <nav :aria-label="$t('shell.nav.main')" class="flex-1 overflow-y-auto p-4">
            <UVerticalNavigation :links="navLinks" />
        </nav>
    </aside>
</template>
