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
                {{ t('admin.settings.title') }}
            </h1>
            <p class="mt-1 text-sm text-[#4d4d4d]">
                {{ t('admin.settings.subtitle') }}
            </p>
        </div>

        <UCard>
            <p class="text-sm text-[#666666]">
                {{ t('admin.settings.api_missing_note') }}
            </p>
        </UCard>
    </div>
</template>
