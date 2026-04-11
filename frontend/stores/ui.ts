import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { ColorMode, Direction } from '~/types/ui';

export const useUIStore = defineStore('ui', () => {
    const isSidebarOpen = ref(true);
    const direction = ref<Direction>('rtl');
    const colorMode = ref<ColorMode>('system');

    function toggleSidebar() {
        isSidebarOpen.value = !isSidebarOpen.value;
    }

    function setSidebarOpen(open: boolean) {
        isSidebarOpen.value = open;
    }

    function setDirection(dir: Direction) {
        direction.value = dir;
    }

    function toggleDirection() {
        direction.value = direction.value === 'rtl' ? 'ltr' : 'rtl';
    }

    function setColorMode(mode: ColorMode) {
        colorMode.value = mode;
    }

    return {
        isSidebarOpen,
        direction,
        colorMode,
        toggleSidebar,
        setSidebarOpen,
        setDirection,
        toggleDirection,
        setColorMode,
    };
});
