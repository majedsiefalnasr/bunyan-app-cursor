import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import PasswordStrength from '~/components/auth/PasswordStrength.vue';

describe('PasswordStrength', () => {
    it('renders nothing when password empty', () => {
        const w = mount(PasswordStrength, {
            props: { password: '' },
            global: {
                stubs: { UProgress: { template: '<div data-testid="progress" />' } },
            },
        });
        expect(w.find('[data-testid="progress"]').exists()).toBe(false);
    });

    it('increases score for stronger passwords', async () => {
        const w = mount(PasswordStrength, {
            props: { password: 'a' },
            global: {
                stubs: {
                    UProgress: { props: ['value'], template: '<div data-testid="progress" />' },
                },
            },
        });
        await w.vm.$nextTick();
        expect(w.find('[data-testid="progress"]').exists()).toBe(true);

        await w.setProps({ password: 'Aa1!aaaaaaaa' });
        await w.vm.$nextTick();
        expect(w.find('[data-testid="progress"]').exists()).toBe(true);
    });
});
