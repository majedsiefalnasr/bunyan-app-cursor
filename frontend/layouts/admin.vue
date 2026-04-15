<template>
    <div
        class="flex h-screen flex-col overflow-hidden bg-[#fafafa] font-sans dark:bg-[#0a0a0a]"
        style="box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.06)"
    >
        <header
            class="flex w-full flex-shrink-0 items-center justify-between gap-3 border-b border-gray-200 bg-white px-4 py-3 dark:border-gray-800 dark:bg-[#171717]"
        >
            <div class="flex items-center gap-3 rtl:flex-row-reverse">
                <div class="text-sm font-medium text-[#171717] dark:text-white">
                    {{ $t('app.name') }}
                </div>
                <UBadge color="gray" variant="subtle" size="sm">
                    {{ $t('admin.title') }}
                </UBadge>
            </div>

            <UButton
                color="gray"
                variant="ghost"
                icon="i-heroicons-arrow-right-on-rectangle"
                @click="logout"
            >
                {{ $t('auth.logout') }}
            </UButton>
        </header>

        <div class="flex min-h-0 min-w-0 flex-1 overflow-hidden">
            <aside
                class="hidden w-64 shrink-0 overflow-y-auto border-s border-gray-200 bg-white dark:border-gray-800 dark:bg-[#171717] lg:block"
            >
                <div class="space-y-2 p-3">
                    <NuxtLink :to="localePath('/admin')" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-squares-2x2">
                            {{ $t('admin.nav.dashboard') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink :to="localePath('/admin/users')" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-users">
                            {{ $t('admin.nav.users') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink :to="localePath('/admin/roles')" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-shield-check">
                            {{ $t('admin.nav.roles') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink :to="localePath('/admin/categories')" class="block">
                        <UButton
                            block
                            color="gray"
                            variant="ghost"
                            icon="i-heroicons-rectangle-stack"
                        >
                            {{ $t('admin.nav.categories') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink :to="localePath('/admin/suppliers')" class="block">
                        <UButton
                            block
                            color="gray"
                            variant="ghost"
                            icon="i-heroicons-building-storefront"
                        >
                            {{ $t('admin.nav.suppliers') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink :to="localePath('/admin/settings')" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-cog-6-tooth">
                            {{ $t('admin.nav.settings') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink :to="localePath('/admin/notifications')" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-bell">
                            {{ $t('admin.nav.notifications') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink :to="localePath('/admin/activity-log')" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-clock">
                            {{ $t('admin.nav.activity_log') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink :to="localePath('/admin/reports')" class="block">
                        <UButton
                            block
                            color="gray"
                            variant="ghost"
                            icon="i-heroicons-document-chart-bar"
                        >
                            {{ $t('admin.nav.reports') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink :to="localePath('/admin/analytics')" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-chart-bar">
                            {{ $t('admin.nav.analytics') }}
                        </UButton>
                    </NuxtLink>
                </div>
            </aside>

            <main
                id="main-content"
                class="min-h-0 min-w-0 flex-1 overflow-y-auto bg-[#fafafa] dark:bg-[#0a0a0a]"
            >
                <div class="mx-auto w-full max-w-6xl p-4">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
    const auth = useAuthStore();
    const localePath = useLocalePath();

    async function logout() {
        await auth.logout();
        await navigateTo(localePath('/auth/login'));
    }
</script>
