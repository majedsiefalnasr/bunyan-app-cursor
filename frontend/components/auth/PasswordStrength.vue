<script setup lang="ts">
    import { computed } from 'vue';

    const { t } = useI18n();

    const props = defineProps<{
        password: string;
    }>();

    const score = computed(() => {
        const p = props.password || '';
        if (p.length === 0) return 0;
        let s = 0;
        if (p.length >= 8) s += 25;
        if (p.length >= 12) s += 15;
        if (/[A-Z]/.test(p)) s += 20;
        if (/[a-z]/.test(p)) s += 15;
        if (/[0-9]/.test(p)) s += 15;
        if (/[^A-Za-z0-9]/.test(p)) s += 10;
        return Math.min(100, s);
    });

    const strengthLabel = computed(() => {
        const v = score.value;
        if (v < 30) return t('auth.password_strength_weak');
        if (v < 60) return t('auth.password_strength_fair');
        if (v < 85) return t('auth.password_strength_good');
        return t('auth.password_strength_strong');
    });
</script>

<template>
    <div v-if="password" class="space-y-1">
        <div class="flex items-center justify-between text-xs text-[#666666]">
            <span>{{ t('auth.password_strength') }}</span>
            <span>{{ strengthLabel }}</span>
        </div>
        <UProgress :value="score" size="xs" color="primary" />
    </div>
</template>
