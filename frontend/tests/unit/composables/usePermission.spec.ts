import { createPinia, setActivePinia } from 'pinia';
import { beforeEach, describe, expect, it } from 'vitest';

import { usePermission } from '~/composables/usePermission';
import { useAuthStore } from '~/stores/auth';

describe('usePermission', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('hasPermission returns false when no permissions', () => {
        const { hasPermission } = usePermission();
        expect(hasPermission('project.view')).toBe(false);
    });

    it('hasPermission returns true when permission exists', () => {
        const store = useAuthStore();
        store.permissions = ['project.view', 'project.create'];

        const { hasPermission } = usePermission();
        expect(hasPermission('project.view')).toBe(true);
    });

    it('hasPermission returns false for missing permission', () => {
        const store = useAuthStore();
        store.permissions = ['project.view'];

        const { hasPermission } = usePermission();
        expect(hasPermission('product.create')).toBe(false);
    });

    it('hasAnyPermission returns true if at least one matches', () => {
        const store = useAuthStore();
        store.permissions = ['project.view', 'order.create'];

        const { hasAnyPermission } = usePermission();
        expect(hasAnyPermission(['project.view', 'product.create'])).toBe(true);
    });

    it('hasAnyPermission returns false if none match', () => {
        const store = useAuthStore();
        store.permissions = ['project.view'];

        const { hasAnyPermission } = usePermission();
        expect(hasAnyPermission(['product.create', 'user.view'])).toBe(false);
    });

    it('hasAllPermissions returns true when all match', () => {
        const store = useAuthStore();
        store.permissions = ['project.view', 'project.create', 'order.view'];

        const { hasAllPermissions } = usePermission();
        expect(hasAllPermissions(['project.view', 'project.create'])).toBe(true);
    });

    it('hasAllPermissions returns false when not all match', () => {
        const store = useAuthStore();
        store.permissions = ['project.view'];

        const { hasAllPermissions } = usePermission();
        expect(hasAllPermissions(['project.view', 'project.create'])).toBe(false);
    });

    it('permissions computed reflects store state', () => {
        const store = useAuthStore();
        const { permissions } = usePermission();

        expect(permissions.value).toEqual([]);

        store.permissions = ['project.view'];
        expect(permissions.value).toEqual(['project.view']);
    });
});
