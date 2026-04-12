import { beforeEach, vi } from 'vitest';

import { resetNuxtAppTestShims } from './shims/nuxt-app';

beforeEach(() => {
    resetNuxtAppTestShims();
});

vi.stubGlobal('definePageMeta', vi.fn());

const toastAdd = vi.fn();

vi.stubGlobal('useToast', () => ({
    add: toastAdd,
}));

vi.stubGlobal('useI18n', () => ({
    t: (key: string) => key,
    te: (key: string) => key.startsWith('errors.codes.'),
}));

vi.stubGlobal('useRuntimeConfig', () => ({
    public: { apiBaseUrl: '' },
}));

vi.stubGlobal('navigateTo', vi.fn());

vi.stubGlobal('useLocalePath', () => (path: string) => {
    const normalized = path.startsWith('/') ? path : `/${path}`;
    return `/ar${normalized}`;
});

vi.stubGlobal('useRouter', () => ({
    back: vi.fn(),
    push: vi.fn(),
}));

vi.stubGlobal(
    '$fetch',
    Object.assign(vi.fn(), {
        create: vi.fn(() => vi.fn()),
    })
);

export { toastAdd };
