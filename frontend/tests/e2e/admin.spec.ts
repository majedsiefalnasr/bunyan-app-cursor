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

test.describe('Admin e2e', () => {
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
