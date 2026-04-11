import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it, vi } from 'vitest';

import { useDirection } from '~/composables/useDirection';
import { useUIStore } from '~/stores/ui';

const STORAGE_KEY = 'bunyan-direction';

describe('useDirection', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
        localStorage.clear();
        vi.clearAllMocks();
        document.documentElement.dir = 'rtl';
        document.documentElement.lang = 'ar';
    });

    it('defaults to rtl from UIStore initial state', () => {
        const { direction } = useDirection();
        expect(direction.value).toBe('rtl');
    });

    it('setDirection("ltr") updates direction to ltr', () => {
        const { direction, setDirection } = useDirection();
        setDirection('ltr');
        expect(direction.value).toBe('ltr');
    });

    it('setDirection persists to localStorage', () => {
        const { setDirection } = useDirection();
        setDirection('ltr');
        expect(localStorage.getItem(STORAGE_KEY)).toBe('ltr');
    });

    it('toggleDirection switches rtl → ltr', () => {
        const { direction, toggleDirection } = useDirection();
        expect(direction.value).toBe('rtl');
        toggleDirection();
        expect(direction.value).toBe('ltr');
    });

    it('toggleDirection switches ltr → rtl on second call', () => {
        const { direction, toggleDirection } = useDirection();
        toggleDirection();
        toggleDirection();
        expect(direction.value).toBe('rtl');
    });

    it('initDirection restores saved direction from localStorage', () => {
        localStorage.setItem(STORAGE_KEY, 'ltr');
        const { direction, initDirection } = useDirection();
        initDirection();
        expect(direction.value).toBe('ltr');
    });

    it('initDirection ignores invalid localStorage values', () => {
        localStorage.setItem(STORAGE_KEY, 'invalid');
        const { direction, initDirection } = useDirection();
        initDirection();
        expect(direction.value).toBe('rtl');
    });

    it('setDirection syncs UIStore and document.dir', () => {
        const { setDirection } = useDirection();
        const uiStore = useUIStore();
        setDirection('ltr');
        expect(uiStore.direction).toBe('ltr');
        expect(document.documentElement.dir).toBe('ltr');
    });
});
