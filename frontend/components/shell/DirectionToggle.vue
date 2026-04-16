<script setup lang="ts">
    const { direction, setDirection } = useDirection();
    const { t, locale } = useI18n();
    const switchLocalePath = useSwitchLocalePath();

    const label = computed(() =>
        direction.value === 'rtl' ? t('shell.direction.ltr') : t('shell.direction.rtl')
    );

    const icon = computed(() =>
        direction.value === 'rtl' ? 'i-heroicons-language' : 'i-heroicons-language'
    );

    async function toggleLanguage() {
        const targetLocale = locale.value === 'ar' ? 'en' : 'ar';
        setDirection(targetLocale === 'ar' ? 'rtl' : 'ltr');
        await navigateTo(switchLocalePath(targetLocale));
    }
</script>

<template>
    <UButton
        color="neutral"
        variant="ghost"
        :icon="icon"
        :aria-label="label"
        data-testid="rtl-toggle"
        size="sm"
        @click="toggleLanguage"
    >
        <span class="sr-only">{{ label }}</span>
        <span aria-hidden="true" class="text-xs font-medium">
            {{ direction === 'rtl' ? 'EN' : 'ع' }}
        </span>
    </UButton>
</template>
