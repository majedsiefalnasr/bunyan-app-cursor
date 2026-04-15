import { describe, expect, it } from 'vitest';

import { buildAdminUsersQuery } from '~/composables/admin/useAdminUsers';

describe('buildAdminUsersQuery', () => {
    it('builds query params for pagination', () => {
        expect(buildAdminUsersQuery({ page: 2, perPage: 15 })).toBe('page=2&per_page=15');
    });

    it('includes role when provided', () => {
        expect(buildAdminUsersQuery({ page: 1, perPage: 10, role: 'admin' })).toBe(
            'page=1&per_page=10&role=admin'
        );
    });
});
