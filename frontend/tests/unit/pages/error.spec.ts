import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import Page404 from '~/pages/error/404.vue';

describe('error pages', () => {
    it('404 renders headings', () => {
        const wrapper = mount(Page404, {
            global: {
                mocks: {
                    $t: (k: string) => k,
                },
                stubs: {
                    UButton: true,
                },
            },
        });

        expect(wrapper.text()).toContain('404');
    });
});
