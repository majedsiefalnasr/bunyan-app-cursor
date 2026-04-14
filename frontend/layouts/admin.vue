<template>
    <!-- Prefer Nuxt UI dashboard primitives (when available) -->
    <UDashboardLayout>
        <template #header>
            <div
                class="flex w-full items-center justify-between gap-3 px-4 py-3 shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] bg-white"
            >
                <div class="flex items-center gap-3 rtl:flex-row-reverse">
                    <div class="text-sm font-medium text-[#171717]">
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
            </div>
        </template>

        <template #sidebar>
            <UDashboardSidebar>
                <div class="p-3 space-y-2">
                    <NuxtLink to="/admin" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-squares-2x2">
                            {{ $t('admin.nav.dashboard') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink to="/admin/users" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-users">
                            {{ $t('admin.nav.users') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink to="/admin/roles" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-shield-check">
                            {{ $t('admin.nav.roles') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink to="/admin/categories" class="block">
                        <UButton
                            block
                            color="gray"
                            variant="ghost"
                            icon="i-heroicons-rectangle-stack"
                        >
                            {{ $t('admin.nav.categories') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink to="/admin/suppliers" class="block">
                        <UButton
                            block
                            color="gray"
                            variant="ghost"
                            icon="i-heroicons-building-storefront"
                        >
                            {{ $t('admin.nav.suppliers') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink to="/admin/settings" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-cog-6-tooth">
                            {{ $t('admin.nav.settings') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink to="/admin/notifications" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-bell">
                            {{ $t('admin.nav.notifications') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink to="/admin/activity-log" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-clock">
                            {{ $t('admin.nav.activity_log') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink to="/admin/reports" class="block">
                        <UButton
                            block
                            color="gray"
                            variant="ghost"
                            icon="i-heroicons-document-chart-bar"
                        >
                            {{ $t('admin.nav.reports') }}
                        </UButton>
                    </NuxtLink>
                    <NuxtLink to="/admin/analytics" class="block">
                        <UButton block color="gray" variant="ghost" icon="i-heroicons-chart-bar">
                            {{ $t('admin.nav.analytics') }}
                        </UButton>
                    </NuxtLink>
                </div>
            </UDashboardSidebar>
        </template>

        <div class="mx-auto w-full max-w-6xl p-4">
            <slot />
        </div>
    </UDashboardLayout>
</template>

<script setup lang="ts">
    const auth = useAuthStore();
    const localePath = useLocalePath();

    async function logout() {
        await auth.logout();
        await navigateTo(localePath('/auth/login'));
    }
</script>
