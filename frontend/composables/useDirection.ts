import { computed } from 'vue';
import type { Direction } from '~/types/ui';
import { useUIStore } from '~/stores/ui';

const STORAGE_KEY = 'bunyan-direction';

export function useDirection() {
    const uiStore = useUIStore();
    const direction = computed(() => uiStore.direction);

    function setDirection(dir: Direction) {
        uiStore.setDirection(dir);
        if (typeof window !== 'undefined') {
            document.documentElement.dir = dir;
            document.documentElement.lang = dir === 'rtl' ? 'ar' : 'en';
            localStorage.setItem(STORAGE_KEY, dir);
        }
    }

    function toggleDirection() {
        setDirection(direction.value === 'rtl' ? 'ltr' : 'rtl');
    }

    function initDirection() {
        if (typeof window !== 'undefined') {
            const saved = localStorage.getItem(STORAGE_KEY) as Direction | null;
            if (saved === 'rtl' || saved === 'ltr') {
                setDirection(saved);
            }
        }
    }

    return { direction, setDirection, toggleDirection, initDirection };
}
