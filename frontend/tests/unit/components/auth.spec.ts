import { mount } from '@vue/test-utils';
import { describe, expect, it } from 'vitest';

import AuthCard from '~/components/auth/AuthCard.vue';
import RoleSelector from '~/components/auth/RoleSelector.vue';

describe('auth components', () => {
    it('AuthCard renders title and description', () => {
        const w = mount(AuthCard, {
            props: { title: 'Title', description: 'Desc' },
        });
        expect(w.text()).toContain('Title');
        expect(w.text()).toContain('Desc');
    });

    it('RoleSelector emits update:modelValue when option clicked', async () => {
        const w = mount(RoleSelector, {
            props: { modelValue: null },
            global: {
                mocks: {
                    $t: (key: string) => key,
                },
                stubs: {
                    UFormField: { template: '<div><slot /></div>' },
                    UButton: {
                        inheritAttrs: false,
                        template:
                            '<button type="button" v-bind="$attrs" @click="$emit(\'click\')"><slot /></button>',
                    },
                },
            },
        });
        await w.get('[data-testid="role-customer"]').trigger('click');
        expect(w.emitted('update:modelValue')?.[0]).toEqual(['customer']);
    });
});
