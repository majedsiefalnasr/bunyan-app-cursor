<script setup lang="ts">
    import { navigationItems } from '~/config/navigation';
    import type { UserRole } from '~/types/auth';
    import type { DropdownMenuItem } from '@nuxt/ui';
    import { useEventListener, useMediaQuery } from '@vueuse/core';

    const open = ref(true);
    const isSearchOpen = ref(false);
    const searchQuery = ref('');

    const { t } = useI18n();
    const localePath = useLocalePath();
    const colorMode = useColorMode();
    const isMobile = useMediaQuery('(max-width: 1023px)');

    const { role, user, isAuthenticated, logout } = useAuth();
    const { direction } = useDirection();
    const isRtl = computed(() => direction.value === 'rtl');
    const navMenuUi = computed(() => ({
        link: ['p-1.5 overflow-hidden'].filter(Boolean).join(' '),
    }));

    const teams = computed(() => [
        {
            label: t('shell.teams.platform'),
            avatar: { src: '/favicon.ico', alt: t('app.name') },
        },
        {
            label: t('shell.teams.marketplace'),
            avatar: { src: '/favicon.ico', alt: t('app.name') },
        },
    ]);

    const selectedTeam = ref(teams.value[0]);
    watch(
        teams,
        (value) => {
            if (!value.length) return;
            if (!selectedTeam.value) selectedTeam.value = value[0];
        },
        { immediate: true }
    );

    const teamsItems = computed<DropdownMenuItem[][]>(() => [
        teams.value.map((team) => ({
            ...team,
            onSelect() {
                selectedTeam.value = team;
            },
        })),
    ]);

    const navItems = computed(() => {
        const userRole = role.value as UserRole | null;

        return navigationItems
            .filter(
                (item) => item.roles.length === 0 || (userRole && item.roles.includes(userRole))
            )
            .map((item) => ({
                label: t(item.labelKey),
                icon: item.icon,
                to: localePath(item.to),
                badge: item.badge ? String(item.badge) : undefined,
            }));
    });

    function getItems(state: 'collapsed' | 'expanded') {
        // Keep the same items list in both states; Nuxt UI will render icon-only in collapsed mode.
        return navItems.value.map((item) => ({
            ...item,
            label: state === 'collapsed' ? '' : item.label,
        }));
    }

    const userButton = computed(() => ({
        label: user.value?.name ?? t('shell.user.guest'),
        avatar: user.value?.avatar
            ? { src: user.value.avatar, alt: user.value?.name ?? t('shell.user.guest') }
            : undefined,
    }));

    const userItems = computed<DropdownMenuItem[][]>(() => [
        [
            {
                label: t('shell.user.profile'),
                icon: 'i-heroicons-user',
                to: localePath('/profile'),
            },
            {
                label: t('notifications.settings'),
                icon: 'i-heroicons-cog-6-tooth',
                to: localePath('/notifications/settings'),
            },
        ],
        [
            {
                label: t('shell.user.appearance'),
                icon: 'i-heroicons-sparkles',
                children: [
                    {
                        label: t('shell.theme.light'),
                        icon: 'i-heroicons-sun',
                        type: 'checkbox',
                        checked: colorMode.value === 'light',
                        onUpdateChecked(checked: boolean) {
                            if (checked) colorMode.preference = 'light';
                        },
                        onSelect(e: Event) {
                            e.preventDefault();
                        },
                    },
                    {
                        label: t('shell.theme.dark'),
                        icon: 'i-heroicons-moon',
                        type: 'checkbox',
                        checked: colorMode.value === 'dark',
                        onUpdateChecked(checked: boolean) {
                            if (checked) colorMode.preference = 'dark';
                        },
                        onSelect(e: Event) {
                            e.preventDefault();
                        },
                    },
                    {
                        label: t('shell.theme.system'),
                        icon: 'i-heroicons-computer-desktop',
                        type: 'checkbox',
                        checked: colorMode.preference === 'system',
                        onUpdateChecked(checked: boolean) {
                            if (checked) colorMode.preference = 'system';
                        },
                        onSelect(e: Event) {
                            e.preventDefault();
                        },
                    },
                ],
            },
        ],
        isAuthenticated.value
            ? [
                  {
                      label: t('shell.user.logout'),
                      icon: 'i-heroicons-arrow-left-on-rectangle',
                      onSelect() {
                          logout();
                      },
                  },
              ]
            : [
                  {
                      label: t('auth.login'),
                      icon: 'i-heroicons-arrow-right-on-rectangle',
                      to: localePath('/auth/login'),
                  },
              ],
    ]);

    function openSearchPalette() {
        isSearchOpen.value = true;
    }

    function closeSearchPalette() {
        isSearchOpen.value = false;
        searchQuery.value = '';
    }

    async function submitSearch() {
        const q = searchQuery.value.trim();
        closeSearchPalette();
        await navigateTo(localePath(q ? `/search?q=${encodeURIComponent(q)}` : '/search'));
    }

    useEventListener('keydown', (e: KeyboardEvent) => {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            openSearchPalette();
        }

        if (e.key === 'Escape' && isSearchOpen.value) {
            e.preventDefault();
            closeSearchPalette();
        }
    });

    const quickLinks = computed(() => [
        {
            label: t('search.palette_open_full'),
            icon: 'i-heroicons-magnifying-glass',
            to: localePath('/search'),
        },
        { label: t('nav.products'), icon: 'i-heroicons-shopping-bag', to: localePath('/products') },
        { label: t('nav.projects'), icon: 'i-heroicons-briefcase', to: localePath('/projects') },
        { label: t('nav.orders'), icon: 'i-heroicons-receipt-percent', to: localePath('/orders') },
        {
            label: t('nav.invoices'),
            icon: 'i-heroicons-document-text',
            to: localePath('/invoices'),
        },
    ]);
</script>

<template>
    <div class="flex h-screen overflow-hidden bg-[#fafafa] dark:bg-[#0a0a0a]">
        <USidebar
            v-model:open="open"
            collapsible="icon"
            rail
            :side="isRtl ? 'right' : 'left'"
            :ui="{
                container: 'h-full',
                inner: 'bg-elevated/25 divide-transparent',
                body: 'py-0',
            }"
        >
            <template #header>
                <UDropdownMenu
                    :items="teamsItems"
                    :content="{ align: 'start', collisionPadding: 12 }"
                    :ui="{ content: 'w-(--reka-dropdown-menu-trigger-width) min-w-48' }"
                >
                    <UButton
                        v-bind="selectedTeam"
                        trailing-icon="i-heroicons-chevron-up-down"
                        color="neutral"
                        variant="ghost"
                        square
                        class="w-full data-[state=open]:bg-elevated overflow-hidden"
                        :ui="{ trailingIcon: 'text-dimmed ms-auto' }"
                    />
                </UDropdownMenu>
            </template>

            <template #default="{ state }">
                <div class="px-2 pt-2">
                    <UButton
                        v-if="!isMobile || open"
                        color="neutral"
                        variant="soft"
                        icon="i-heroicons-magnifying-glass"
                        class="w-full justify-start"
                        :label="state === 'collapsed' ? '' : t('shell.search')"
                        @click="openSearchPalette"
                    >
                        <template v-if="state !== 'collapsed'" #trailing>
                            <span
                                class="ms-auto inline-flex items-center gap-1 text-xs text-[#808080] dark:text-[#a3a3a3]"
                            >
                                <UKbd value="meta" />
                                <span>+</span>
                                <UKbd value="K" />
                            </span>
                        </template>
                    </UButton>
                </div>

                <UNavigationMenu
                    :key="state"
                    :items="getItems(state)"
                    orientation="vertical"
                    :ui="navMenuUi"
                />
            </template>

            <template #footer>
                <UDropdownMenu
                    :items="userItems"
                    :content="{ align: 'center', collisionPadding: 12 }"
                    :ui="{ content: 'w-(--reka-dropdown-menu-trigger-width) min-w-48' }"
                >
                    <UButton
                        v-bind="userButton"
                        :label="userButton.label"
                        trailing-icon="i-heroicons-chevron-up-down"
                        color="neutral"
                        variant="ghost"
                        square
                        class="w-full data-[state=open]:bg-elevated overflow-hidden"
                        :ui="{ trailingIcon: 'text-dimmed ms-auto' }"
                    />
                </UDropdownMenu>
            </template>
        </USidebar>

        <div class="flex min-w-0 flex-1 flex-col overflow-hidden">
            <div
                class="h-16 shrink-0 flex items-center justify-between gap-2 px-4 border-b border-default"
            >
                <div class="flex items-center gap-1">
                    <UButton
                        icon="i-heroicons-bars-3"
                        color="neutral"
                        variant="ghost"
                        :aria-label="$t('shell.sidebar.toggle')"
                        @click="open = !open"
                    />

                    <UButton
                        v-if="isMobile && !open"
                        color="neutral"
                        variant="soft"
                        icon="i-heroicons-magnifying-glass"
                        :aria-label="t('shell.search')"
                        @click="openSearchPalette"
                    />
                </div>

                <div class="flex items-center gap-1">
                    <DirectionToggle />
                    <NotificationBell v-if="isAuthenticated" />
                </div>
            </div>

            <main id="main-content" class="flex-1 overflow-y-auto">
                <div class="p-4 sm:p-6">
                    <div class="mx-auto w-full max-w-6xl space-y-4">
                        <AppBreadcrumb />
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>

    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="isSearchOpen"
                class="fixed inset-0 z-100 flex items-center justify-center p-4 sm:p-6"
                role="dialog"
                aria-modal="true"
                :aria-label="t('search.palette_title')"
            >
                <button
                    type="button"
                    class="absolute inset-0 bg-black/40 backdrop-blur-md"
                    aria-label="Close"
                    @click="closeSearchPalette"
                />

                <div class="relative w-full max-w-xl">
                    <UCard class="w-full shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)]">
                        <template #header>
                            <div class="flex items-center justify-between gap-3">
                                <span class="font-medium text-[#171717] dark:text-white">
                                    {{ t('search.palette_title') }}
                                </span>
                                <UButton
                                    color="neutral"
                                    variant="ghost"
                                    icon="i-heroicons-x-mark"
                                    :aria-label="t('shell.sidebar.close')"
                                    @click="closeSearchPalette"
                                />
                            </div>
                        </template>

                        <form class="space-y-4 p-4 sm:p-5" @submit.prevent="submitSearch">
                            <div class="block w-full min-w-0">
                                <UInput
                                    v-model="searchQuery"
                                    class="w-full"
                                    :ui="{
                                        root: 'relative inline-flex w-full min-w-0 items-center',
                                    }"
                                    autofocus
                                    size="lg"
                                    icon="i-heroicons-magnifying-glass"
                                    :placeholder="t('search.palette_placeholder')"
                                />
                            </div>

                            <div class="space-y-2">
                                <p class="text-xs text-[#666666]">
                                    {{ t('search.palette_hint') }}
                                </p>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    <UButton
                                        v-for="item in quickLinks"
                                        :key="item.to"
                                        color="neutral"
                                        variant="soft"
                                        :icon="item.icon"
                                        class="justify-start"
                                        @click="
                                            async () => {
                                                closeSearchPalette();
                                                await navigateTo(item.to);
                                            }
                                        "
                                    >
                                        {{ item.label }}
                                    </UButton>
                                </div>
                            </div>
                        </form>

                        <template #footer>
                            <div
                                class="flex flex-wrap items-center justify-between gap-2 text-xs text-[#808080]"
                            >
                                <span class="inline-flex items-center gap-1">
                                    <UKbd value="enter" />
                                    <span>{{ t('shell.search') }}</span>
                                </span>
                                <span class="inline-flex items-center gap-1">
                                    <UKbd value="esc" />
                                    <span>{{ t('common.cancel') }}</span>
                                </span>
                            </div>
                        </template>
                    </UCard>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
