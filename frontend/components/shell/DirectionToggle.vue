<script setup lang="ts">
    const { direction, toggleDirection } = useDirection();
    const { t } = useI18n();

    const label = computed(() =>
        direction.value === 'rtl' ? t('shell.direction.ltr') : t('shell.direction.rtl')
    );

    const icon = computed(() =>
        direction.value === 'rtl' ? 'i-heroicons-language' : 'i-heroicons-language'
    );
</script>

<template>
    <UButton
        color="gray"
        variant="ghost"
        :icon="icon"
        :aria-label="label"
        data-testid="rtl-toggle"
        size="sm"
        @click="toggleDirection"
    >
        <span class="sr-only">{{ label }}</span>
        <span aria-hidden="true" class="text-xs font-medium">
            {{ direction === 'rtl' ? 'EN' : 'ع' }}
        </span>
    </UButton>
</template>
