<script setup lang="ts">
    const authStore = useAuthStore();
    const uiStore = useUIStore();
    const colorMode = useColorMode();

    const isDark = computed(() => colorMode.value === 'dark');

    function toggleColorMode() {
        colorMode.preference = isDark.value ? 'light' : 'dark';
    }

    const themeIcon = computed(() => (isDark.value ? 'i-heroicons-sun' : 'i-heroicons-moon'));

    const themeLabel = computed(() => (isDark.value ? 'shell.theme.light' : 'shell.theme.dark'));
</script>

<template>
    <header
        class="relative z-30 flex h-16 items-center gap-4 bg-white px-4 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] dark:bg-[#171717] dark:shadow-[0px_0px_0px_1px_rgba(255,255,255,0.06)] sm:px-6"
    >
        <AppLoadingBar />

        <!-- Mobile hamburger -->
        <UButton
            color="gray"
            variant="ghost"
            icon="i-heroicons-bars-3"
            :aria-label="$t('shell.sidebar.toggle')"
            class="lg:hidden"
            data-testid="mobile-nav-toggle"
            @click="uiStore.toggleSidebar"
        />

        <!-- Brand -->
        <NuxtLink
            to="/"
            class="flex items-center gap-2 text-base font-semibold tracking-tight text-[#171717] dark:text-white"
        >
            {{ $t('app.name') }}
        </NuxtLink>

        <!-- Spacer -->
        <div class="flex-1" />

        <!-- Actions -->
        <div class="flex items-center gap-1">
            <!-- Direction toggle -->
            <DirectionToggle />

            <!-- Dark mode toggle -->
            <UButton
                color="gray"
                variant="ghost"
                :icon="themeIcon"
                :aria-label="$t(themeLabel)"
                size="sm"
                data-testid="theme-toggle"
                @click="toggleColorMode"
            />

            <!-- Language switcher -->
            <LanguageSwitcher />

            <NotificationBell v-if="authStore.isAuthenticated" />

            <!-- User menu -->
            <AppUserMenu />
        </div>
    </header>
</template>
