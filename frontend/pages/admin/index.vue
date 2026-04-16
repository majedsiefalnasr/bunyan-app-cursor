<script setup lang="ts">
    definePageMeta({
        layout: 'admin',
        middleware: ['auth', 'role'],
        roles: ['admin'],
    });

    const { t } = useI18n();
</script>

<template>
    <div class="space-y-6">
        <div>
            <h1
                class="text-2xl font-semibold tracking-tight text-[#171717]"
                style="letter-spacing: -0.06em"
            >
                {{ t('admin.dashboard.title') }}
            </h1>
            <p class="mt-1 text-sm text-[#4d4d4d]">
                {{ t('admin.dashboard.subtitle') }}
            </p>
        </div>

        <UCard>
            <template #header>
                <div class="text-sm font-medium text-[#171717]">
                    {{ t('admin.dashboard.quick_links') }}
                </div>
            </template>
            <div class="flex flex-wrap gap-2">
                <UButton color="neutral" variant="soft" to="/admin/users" icon="i-heroicons-users">
                    {{ t('admin.nav.users') }}
                </UButton>
                <UButton
                    color="neutral"
                    variant="soft"
                    to="/admin/roles"
                    icon="i-heroicons-shield-check"
                >
                    {{ t('admin.nav.roles') }}
                </UButton>
                <UButton
                    color="neutral"
                    variant="soft"
                    to="/admin/categories"
                    icon="i-heroicons-rectangle-stack"
                >
                    {{ t('admin.nav.categories') }}
                </UButton>
                <UButton
                    color="neutral"
                    variant="soft"
                    to="/admin/activity-log"
                    icon="i-heroicons-clock"
                >
                    {{ t('admin.nav.activity_log') }}
                </UButton>
                <UButton
                    color="neutral"
                    variant="soft"
                    to="/admin/reports"
                    icon="i-heroicons-document-chart-bar"
                >
                    {{ t('admin.nav.reports') }}
                </UButton>
                <UButton
                    color="neutral"
                    variant="soft"
                    to="/admin/analytics"
                    icon="i-heroicons-chart-bar"
                >
                    {{ t('admin.nav.analytics') }}
                </UButton>
            </div>
        </UCard>
    </div>
</template>
