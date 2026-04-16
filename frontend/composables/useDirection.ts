import { computed, watch } from 'vue';
import type { Direction } from '~/types/ui';
import { useUIStore } from '~/stores/ui';

export function useDirection() {
    const uiStore = useUIStore();
    const { locale } = useI18n();
    const direction = computed(() => uiStore.direction);

    function setDirection(dir: Direction) {
        uiStore.setDirection(dir);
        if (typeof window !== 'undefined') {
            document.documentElement.dir = dir;
        }
    }

    function toggleDirection() {
        setDirection(direction.value === 'rtl' ? 'ltr' : 'rtl');
    }

    function initDirection() {
        // Always derive direction from current locale (ar=rtl, en=ltr).
        setDirection(locale.value === 'ar' ? 'rtl' : 'ltr');
    }

    // Keep direction in sync with locale changes (language switch).
    watch(
        locale,
        (value) => {
            setDirection(value === 'ar' ? 'rtl' : 'ltr');
        },
        { immediate: true }
    );

    return { direction, setDirection, toggleDirection, initDirection };
}
