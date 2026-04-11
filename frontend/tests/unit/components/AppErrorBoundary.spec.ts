import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import AppErrorBoundary from '~/components/common/AppErrorBoundary.vue';

describe('AppErrorBoundary', () => {
    it('renders slot when no error', () => {
        const wrapper = mount(AppErrorBoundary, {
            slots: {
                default: '<div class="ok">ok</div>',
            },
            global: {
                mocks: {
                    $t: (k: string) => k,
                },
                stubs: { UButton: true },
            },
        });

        expect(wrapper.find('.ok').exists()).toBe(true);
    });
});
