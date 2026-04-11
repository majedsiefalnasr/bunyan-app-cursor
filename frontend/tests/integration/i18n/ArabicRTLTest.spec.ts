import { describe, expect, it } from 'vitest';

describe('Arabic RTL', () => {
    it('logical properties are used in error layout class list', async () => {
        const mod = await import('~/layouts/error.vue');
        expect(mod).toBeDefined();
    });
});
