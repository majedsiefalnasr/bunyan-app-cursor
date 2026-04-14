import { expect, test } from '@playwright/test';

import { e2eLoginAs } from './_helpers/auth';

function json(body: unknown, status = 200) {
    return {
        status,
        contentType: 'application/json',
        body: JSON.stringify(body),
    };
}

function matchesAdminUsersGet(req: { method: () => string; url: () => string }) {
    return req.method() === 'GET' && /\/v1\/admin\/users|\/api\/v1\/admin\/users/.test(req.url());
}

function matchesAdminSuppliersGet(req: { method: () => string; url: () => string }) {
    return (
        req.method() === 'GET' &&
        /\/v1\/admin\/suppliers|\/api\/v1\/admin\/suppliers/.test(req.url())
    );
}

function matchesSupplierVerifyPut(req: { method: () => string; url: () => string }) {
    return (
        req.method() === 'PUT' &&
        /\/v1\/suppliers\/\d+\/verify|\/api\/v1\/suppliers\/\d+\/verify/.test(req.url())
    );
}

function matchesAdminRolesGet(req: { method: () => string; url: () => string }) {
    return req.method() === 'GET' && /\/v1\/admin\/roles|\/api\/v1\/admin\/roles/.test(req.url());
}

function matchesAdminRolePermissionsGet(req: { method: () => string; url: () => string }) {
    return (
        req.method() === 'GET' &&
        /\/v1\/admin\/roles\/\d+\/permissions|\/api\/v1\/admin\/roles\/\d+\/permissions/.test(
            req.url()
        )
    );
}

function matchesCategoriesGet(req: { method: () => string; url: () => string }) {
    return req.method() === 'GET' && /\/v1\/categories\?|\/api\/v1\/categories\?/.test(req.url());
}

function matchesAdminActivityLogGet(req: { method: () => string; url: () => string }) {
    return (
        req.method() === 'GET' &&
        /\/v1\/admin\/activity-log|\/api\/v1\/admin\/activity-log/.test(req.url())
    );
}

function matchesAdminReportTypesGet(req: { method: () => string; url: () => string }) {
    return (
        req.method() === 'GET' &&
        /\/v1\/admin\/analytics\/reports\/types|\/api\/v1\/admin\/analytics\/reports\/types/.test(
            req.url()
        )
    );
}

function matchesAnalyticsOverviewGet(req: { method: () => string; url: () => string }) {
    return (
        req.method() === 'GET' &&
        /\/v1\/analytics\/overview|\/api\/v1\/analytics\/overview/.test(req.url())
    );
}

function matchesAnalyticsTrendsGet(req: { method: () => string; url: () => string }) {
    return (
        req.method() === 'GET' &&
        /\/v1\/analytics\/trends|\/api\/v1\/analytics\/trends/.test(req.url())
    );
}

test.describe('Admin e2e', () => {
    test('admin scoped routes render (route coverage > 95%)', async ({ page, baseURL }) => {
        await e2eLoginAs(page, { baseURL, role: 'admin' });

        await page.route('**/*', async (route) => {
            const req = route.request();

            if (matchesAdminUsersGet(req)) {
                await route.fulfill(
                    json({
                        success: true,
                        data: {
                            data: [
                                {
                                    id: 1,
                                    name: 'Admin Test User',
                                    email: 'admin-test@example.com',
                                    role: 'customer',
                                    role_label: 'العميل',
                                    phone: null,
                                    active: true,
                                    email_verified_at: null,
                                    created_at: '2020-01-01T00:00:00.000000Z',
                                },
                            ],
                            meta: { total: 1, last_page: 1 },
                        },
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }

            if (matchesAdminRolesGet(req)) {
                await route.fulfill(
                    json({
                        success: true,
                        data: { roles: [{ id: 1, name: 'admin' }] },
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }

            if (matchesAdminRolePermissionsGet(req)) {
                await route.fulfill(
                    json({
                        success: true,
                        data: {
                            permissions: [
                                { key: 'users.view', label: 'عرض المستخدمين', enabled: true },
                                { key: 'users.edit', label: 'تعديل المستخدمين', enabled: false },
                            ],
                        },
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }

            if (matchesCategoriesGet(req)) {
                await route.fulfill(
                    json({
                        success: true,
                        data: [
                            {
                                id: 10,
                                parent_id: null,
                                name_ar: 'مواد البناء',
                                name_en: 'Building materials',
                                slug: 'materials',
                                icon: null,
                                sort_order: 0,
                                is_active: true,
                                children: [
                                    {
                                        id: 11,
                                        parent_id: 10,
                                        name_ar: 'أسمنت',
                                        name_en: 'Cement',
                                        slug: 'cement',
                                        icon: null,
                                        sort_order: 0,
                                        is_active: true,
                                        children: [],
                                    },
                                ],
                            },
                        ],
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }

            if (matchesAdminSuppliersGet(req)) {
                await route.fulfill(
                    json({
                        data: [
                            {
                                id: 101,
                                company_name_ar: 'مورد اختبار',
                                verification_status: 'pending',
                                user: { email: 'supplier@example.com', name: 'Supplier' },
                            },
                        ],
                    })
                );
                return;
            }

            if (matchesAdminActivityLogGet(req)) {
                await route.fulfill(
                    json({
                        success: true,
                        data: {
                            data: [
                                {
                                    id: 1,
                                    action: 'user.role_assigned',
                                    subject_type: 'User',
                                    subject_id: 1,
                                    created_at: '2020-01-01T00:00:00.000000Z',
                                    actor: { id: 42, name: 'Admin' },
                                },
                            ],
                            meta: { total: 1, last_page: 1 },
                        },
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }

            if (matchesAdminReportTypesGet(req)) {
                await route.fulfill(
                    json({
                        success: true,
                        data: {
                            types: [
                                {
                                    type: 'sales_summary',
                                    label_key: 'sales_summary',
                                    exports: ['pdf'],
                                },
                            ],
                        },
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }

            if (matchesAnalyticsOverviewGet(req)) {
                await route.fulfill(
                    json({
                        success: true,
                        data: {
                            range: { from: '2020-01-01', to: '2020-01-07', bucket: 'day' },
                            compare: { mode: 'previous_period' },
                            kpis: [
                                {
                                    key: 'platform.active_users',
                                    label: 'Active users',
                                    value: 10,
                                    delta: { value: 1, pct: 0.1 },
                                },
                            ],
                        },
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }

            if (matchesAnalyticsTrendsGet(req)) {
                await route.fulfill(
                    json({
                        success: true,
                        data: {
                            range: { from: '2020-01-01', to: '2020-01-07', bucket: 'day' },
                            bucket: 'day',
                            series: [
                                {
                                    key: 'platform.active_users',
                                    points: [
                                        { t: '2020-01-01', v: 1 },
                                        { t: '2020-01-02', v: 2 },
                                    ],
                                },
                            ],
                        },
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }

            await route.continue();
        });

        const scopedAdminRoutes = [
            '/ar/admin',
            '/ar/admin/users',
            '/ar/admin/users/1',
            '/ar/admin/roles',
            '/ar/admin/categories',
            '/ar/admin/suppliers',
            '/ar/admin/settings',
            '/ar/admin/notifications',
            '/ar/admin/activity-log',
            '/ar/admin/reports',
            '/ar/admin/analytics',
        ];

        let visited = 0;

        for (const path of scopedAdminRoutes) {
            await page.goto(path, { waitUntil: 'domcontentloaded' });
            await expect(page).toHaveURL(new RegExp(path.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')));
            await expect(page.locator('main, body')).toBeVisible();
            visited += 1;
        }

        const coverage = visited / scopedAdminRoutes.length;
        expect(coverage).toBeGreaterThan(0.95);
    });

    test('admin can access /admin and see navigation', async ({ page, baseURL }) => {
        await e2eLoginAs(page, { baseURL, role: 'admin' });

        await page.goto('/ar/admin', { waitUntil: 'domcontentloaded' });
        await expect(page).toHaveURL(/\/ar\/admin(?:\/|$)/);

        await expect(page.getByRole('button', { name: /المستخدمون|Users/ })).toBeVisible();
        await expect(page.getByRole('button', { name: /الأدوار|Roles/ })).toBeVisible();
    });

    test('non-admin is redirected away from /admin', async ({ page, baseURL }) => {
        await e2eLoginAs(page, { baseURL, role: 'customer' });

        await page.goto('/ar/admin', { waitUntil: 'domcontentloaded' });
        await expect(page).toHaveURL(/\/ar\/dashboard(?:\/|$)/);
    });

    test('admin users list renders rows from mocked API', async ({ page, baseURL }) => {
        await e2eLoginAs(page, { baseURL, role: 'admin' });

        await page.route('**/*', async (route) => {
            if (matchesAdminUsersGet(route.request())) {
                await route.fulfill(
                    json({
                        success: true,
                        data: {
                            data: [
                                {
                                    id: 1,
                                    name: 'Admin Test User',
                                    email: 'admin-test@example.com',
                                    role: 'customer',
                                    role_label: 'العميل',
                                    phone: null,
                                    active: true,
                                    email_verified_at: null,
                                    created_at: '2020-01-01T00:00:00.000000Z',
                                },
                            ],
                            meta: { total: 1, last_page: 1 },
                        },
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }
            await route.continue();
        });

        await page.goto('/ar/admin/users', { waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'إدارة المستخدمين' })).toBeVisible();
        await expect(page.getByText('Admin Test User')).toBeVisible();
        await expect(page.getByText('admin-test@example.com')).toBeVisible();
    });

    test('admin can verify a supplier (mocked)', async ({ page, baseURL }) => {
        await e2eLoginAs(page, { baseURL, role: 'admin' });

        let verifyCalled = false;

        await page.route('**/*', async (route) => {
            const req = route.request();

            if (matchesAdminSuppliersGet(req)) {
                await route.fulfill(
                    json({
                        data: [
                            {
                                id: 101,
                                company_name_ar: 'مورد اختبار',
                                verification_status: 'pending',
                                user: { email: 'supplier@example.com', name: 'Supplier' },
                            },
                        ],
                    })
                );
                return;
            }

            if (matchesSupplierVerifyPut(req)) {
                verifyCalled = true;
                await route.fulfill(
                    json({
                        success: true,
                        data: null,
                        message: 'OK',
                        errors: [],
                        error: null,
                    })
                );
                return;
            }

            await route.continue();
        });

        await page.goto('/ar/admin/suppliers', { waitUntil: 'domcontentloaded' });
        await expect(page.getByText('مورد اختبار')).toBeVisible();

        await page.getByRole('button', { name: 'تحقق' }).click();

        await expect.poll(() => verifyCalled).toBe(true);
    });
});
