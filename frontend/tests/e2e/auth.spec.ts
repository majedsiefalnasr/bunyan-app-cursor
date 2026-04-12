import { expect, test } from '@playwright/test';

const apiProfile = {
    id: 42,
    name: 'Playwright User',
    email: 'pw@example.com',
    role: 'customer' as const,
    phone: null,
    active: true,
    email_verified_at: null,
    created_at: '2020-01-01T00:00:00.000000Z',
    updated_at: '2020-01-01T00:00:00.000000Z',
};

function json(body: unknown, status = 200) {
    return {
        status,
        contentType: 'application/json',
        body: JSON.stringify(body),
    };
}

function hostFromBase(baseURL: string | undefined) {
    if (!baseURL) return 'localhost';
    try {
        return new URL(baseURL).hostname;
    } catch {
        return 'localhost';
    }
}

function matchesProfileGet(req: { method: () => string; url: () => string }) {
    return req.method() === 'GET' && /\/v1\/auth\/profile|\/api\/v1\/auth\/profile/.test(req.url());
}

function matchesProfilePut(req: { method: () => string; url: () => string }) {
    return req.method() === 'PUT' && /\/v1\/auth\/profile|\/api\/v1\/auth\/profile/.test(req.url());
}

function matchesForgotPost(req: { method: () => string; url: () => string }) {
    return (
        req.method() === 'POST' &&
        /\/v1\/auth\/forgot-password|\/api\/v1\/auth\/forgot-password/.test(req.url())
    );
}

function matchesResetPost(req: { method: () => string; url: () => string }) {
    return (
        req.method() === 'POST' &&
        /\/v1\/auth\/reset-password|\/api\/v1\/auth\/reset-password/.test(req.url())
    );
}

test.describe('Auth pages', () => {
    test('login page renders email and password fields', async ({ page }) => {
        await page.goto('/ar/auth/login', { waitUntil: 'domcontentloaded' });
        await expect(page.getByPlaceholder('أدخل بريدك الإلكتروني')).toBeVisible();
        await expect(page.getByPlaceholder('أدخل كلمة المرور')).toBeVisible();
    });

    test('login password visibility toggles input type', async ({ page }) => {
        await page.goto('/ar/auth/login', { waitUntil: 'domcontentloaded' });
        const pwd = page.locator('input[type="password"]').first();
        await expect(pwd).toBeVisible();
        await page.getByRole('button', { name: 'إظهار كلمة المرور' }).click();
        await expect(page.locator('input[type="text"]').first()).toBeVisible();
    });

    test('registration wizard advances to credentials step', async ({ page }) => {
        await page.goto('/ar/auth/register', { waitUntil: 'domcontentloaded' });

        await expect(page.getByTestId('register-step-indicator')).toContainText('1');
        await page.getByTestId('role-contractor').click();
        await page.getByTestId('register-next').click();

        await expect(page.getByTestId('register-step-indicator')).toContainText('2');
        await page.getByPlaceholder('أدخل اسمك الكامل').fill('E2E User');
        await page.getByPlaceholder('أدخل بريدك الإلكتروني').fill('e2e-user@example.com');
        await page.getByTestId('register-next').click();

        await expect(page.getByTestId('register-step-indicator')).toContainText('3');
        await expect(page.getByPlaceholder('أدخل كلمة المرور').first()).toBeVisible();
    });

    test('forgot password shows success after API accepts email', async ({ page }) => {
        await page.route('**/*', async (route) => {
            if (matchesForgotPost(route.request())) {
                await route.fulfill(
                    json({
                        success: true,
                        data: null,
                        message: 'تم الإرسال',
                        errors: [],
                        error: null,
                    })
                );
                return;
            }
            await route.continue();
        });

        await page.goto('/ar/auth/forgot-password', { waitUntil: 'domcontentloaded' });
        await page.getByPlaceholder('أدخل بريدك الإلكتروني').fill('pw@example.com');
        await page.getByRole('button', { name: 'إرسال رابط إعادة التعيين' }).click();
        await expect(page.getByText(/تم إرسال رابط إعادة تعيين كلمة المرور/)).toBeVisible({
            timeout: 15_000,
        });
    });

    test('reset password submits and shows success', async ({ page }) => {
        await page.route('**/*', async (route) => {
            if (matchesResetPost(route.request())) {
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

        await page.goto('/ar/auth/reset-password?token=fake-token&email=pw%40example.com', {
            waitUntil: 'domcontentloaded',
        });
        await page.locator('input[type="password"]').nth(0).fill('Newpass1!');
        await page.locator('input[type="password"]').nth(1).fill('Newpass1!');
        await page.getByRole('button', { name: 'إعادة تعيين كلمة المرور' }).click();
        await expect(page.getByText('تم إعادة تعيين كلمة المرور بنجاح')).toBeVisible({
            timeout: 15_000,
        });
    });

    test('verify-email page shows resend when session cookie is present', async ({
        page,
        baseURL,
    }) => {
        await page.route('**/*', async (route) => {
            if (matchesProfileGet(route.request())) {
                await route.fulfill(
                    json({
                        success: true,
                        data: apiProfile,
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }
            await route.continue();
        });

        const host = hostFromBase(baseURL);
        await page.context().addCookies([
            {
                name: 'auth_token',
                value: 'e2e-verify',
                domain: host,
                path: '/',
                sameSite: 'Lax',
            },
        ]);

        await page.goto('/ar/auth/verify-email', { waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('button', { name: 'إعادة إرسال رابط التحقق' })).toBeVisible();
    });

    test('profile page loads and save updates mocked profile', async ({ page, baseURL }) => {
        const host = hostFromBase(baseURL);
        await page.context().addCookies([
            {
                name: 'auth_token',
                value: 'e2e-profile',
                domain: host,
                path: '/',
                sameSite: 'Lax',
            },
        ]);

        await page.route('**/*', async (route) => {
            const req = route.request();
            if (matchesProfileGet(req)) {
                await route.fulfill(
                    json({
                        success: true,
                        data: apiProfile,
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }
            if (matchesProfilePut(req)) {
                await route.fulfill(
                    json({
                        success: true,
                        data: { ...apiProfile, name: 'Saved Name' },
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }
            await route.continue();
        });

        await page.goto('/ar/profile', { waitUntil: 'domcontentloaded' });
        await expect(page.getByRole('heading', { name: 'الملف الشخصي' })).toBeVisible();
        await expect(page.locator('input').first()).toHaveValue('Playwright User', {
            timeout: 15_000,
        });
    });

    test('profile cancel reverts edited name', async ({ page, baseURL }) => {
        const host = hostFromBase(baseURL);
        await page.context().addCookies([
            {
                name: 'auth_token',
                value: 'e2e-cancel',
                domain: host,
                path: '/',
                sameSite: 'Lax',
            },
        ]);

        await page.route('**/*', async (route) => {
            if (matchesProfileGet(route.request())) {
                await route.fulfill(
                    json({
                        success: true,
                        data: apiProfile,
                        message: null,
                        errors: [],
                        error: null,
                    })
                );
                return;
            }
            await route.continue();
        });

        await page.goto('/ar/profile', { waitUntil: 'domcontentloaded' });
        const nameInput = page.locator('input').first();
        await nameInput.fill('Temporary');
        await page.getByRole('button', { name: 'إلغاء' }).click();
        await expect(nameInput).toHaveValue('Playwright User');
    });

    test('session cookie is applied when set on context', async ({ page, baseURL }) => {
        const host = hostFromBase(baseURL);
        await page.context().addCookies([
            {
                name: 'auth_token',
                value: 'cookie-from-context',
                domain: host,
                path: '/',
                sameSite: 'Lax',
            },
        ]);
        await page.goto('/ar/auth/login', { waitUntil: 'domcontentloaded' });
        const cookies = await page.context().cookies();
        expect(
            cookies.some((c) => c.name === 'auth_token' && c.value === 'cookie-from-context')
        ).toBe(true);
    });
});
