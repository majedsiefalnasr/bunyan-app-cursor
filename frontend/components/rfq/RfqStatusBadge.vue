<script setup lang="ts">
    const props = defineProps<{
        status: string;
        statusLabel?: string | null;
    }>();

    const { t } = useI18n();

    const label = computed(() => {
        const key = `rfq.status.${props.status}`;
        const translated = t(key);
        if (translated !== key) {
            return translated;
        }
        return props.statusLabel ?? props.status;
    });

    const color = computed(() => {
        switch (props.status) {
            case 'draft':
                return 'neutral';
            case 'sent':
            case 'quoting':
                return 'info';
            case 'evaluation':
                return 'warning';
            case 'awarded':
                return 'success';
            case 'closed':
                return 'neutral';
            default:
                return 'neutral';
        }
    });
</script>

<template>
    <UBadge :color="color" variant="soft" data-testid="rfq-status-badge">
        {{ label }}
    </UBadge>
</template>
