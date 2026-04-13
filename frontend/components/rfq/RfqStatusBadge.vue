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
                return 'gray';
            case 'sent':
            case 'quoting':
                return 'blue';
            case 'evaluation':
                return 'amber';
            case 'awarded':
                return 'green';
            case 'closed':
                return 'gray';
            default:
                return 'gray';
        }
    });
</script>

<template>
    <UBadge :color="color" variant="soft" data-testid="rfq-status-badge">
        {{ label }}
    </UBadge>
</template>
