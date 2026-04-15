import { expect, test, type Page } from '@playwright/test';

import { isAppRestApiUrl } from './_helpers/apiPath';
import { gotoAuthForm } from './_helpers/nuxtReady';

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

/** Name field is `type="text"`; phone is `tel` — avoids `input.first()` matching layout/auth inputs. */
function profileNameInput(page: Page) {
    return page.locator('#main-content .max-w-lg input[type="text"]').first();
}

test.describe('Auth pages', () => {
    test('login page renders email and password fields', async ({ page }) => {
        await page.goto('/ar/auth/login', { waitUntil: 'domcontentloaded' });
        await expect(page.getByPlaceholder('أدخل بريدك الإلكتروني')).toBeVisible();
        await expect(page.getByPlaceholder('أدخل كلمة المرور')).toBeVisible();
    });

    test('login password visibility toggles input type', async ({ page }) => {
        await gotoAuthForm(page, '/ar/auth/login');
        const pwd = page.getByPlaceholder('أدخل كلمة المرور');
        await expect(pwd).toHaveAttribute('type', 'password');
        await page.getByRole('button', { name: 'إظهار كلمة المرور' }).click();
        await expect(pwd).toHaveAttribute('type', 'text');
    });

    test('registration wizard advances to credentials step', async ({ page }) => {
        await page.goto('/ar/auth/register', { waitUntil: 'load' });
        await expect(page.locator('html')).toHaveAttribute('data-pw-hydrated', '1', {
            timeout: 15_000,
        });
        await expect(page.getByTestId('register-step-indicator')).toBeVisible({
            timeout: 15_000,
        });

        await expect(page.getByTestId('register-step-indicator')).toContainText(/الخطوة 1 من/);
        await page.getByTestId('role-contractor').click();
        const next = page.getByTestId('register-next');
        await expect(next).toBeVisible({ timeout: 15_000 });
        await next.click();
        // CI: if the first click lands before reactive state updates, validate fails and step stays 1.
        await page.waitForTimeout(150);
        if ((await page.getByTestId('register-step-indicator').innerText()).includes('الخطوة 1')) {
            await next.click({ force: true });
        }
        await expect
            .poll(() => page.getByTestId('register-step-indicator').innerText(), {
                timeout: 15_000,
            })
            .toMatch(/الخطوة 2 من/);
        await page.getByPlaceholder('أدخل اسمك الكامل').fill('E2E User');
        await page.getByPlaceholder('أدخل بريدك الإلكتروني').fill('e2e-user@example.com');
        await page.getByTestId('register-next').click();

        await expect
            .poll(() => page.getByTestId('register-step-indicator').innerText(), {
                timeout: 15_000,
            })
            .toMatch(/الخطوة 3 من/);
        await expect(page.getByPlaceholder('أدخل كلمة المرور').first()).toBeVisible();
    });

    test('forgot password shows success after API accepts email', async ({ page }) => {
        await page.route(isAppRestApiUrl, async (route) => {
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

        await gotoAuthForm(page, '/ar/auth/forgot-password');
        const forgotEmail = page.getByRole('textbox', { name: 'البريد الإلكتروني' });
        await forgotEmail.fill('pw@example.com');
        await forgotEmail.blur();
        const forgotPost = page.waitForResponse(
            (r) =>
                r.request().method() === 'POST' &&
                /\/(?:api\/)?v1\/auth\/forgot-password/.test(r.url())
        );
        await page.getByRole('button', { name: 'إرسال رابط إعادة التعيين' }).click();
        await forgotPost;
        await expect(page.getByText(/تم إرسال رابط إعادة تعيين كلمة المرور/)).toBeVisible({
            timeout: 15_000,
        });
    });

    test('reset password submits and shows success', async ({ page }) => {
        await page.route(isAppRestApiUrl, async (route) => {
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

        await gotoAuthForm(page, '/ar/auth/reset-password?token=fake-token&email=pw%40example.com');
        const p0 = page.locator('input[type="password"]').nth(0);
        const p1 = page.locator('input[type="password"]').nth(1);
        await p0.fill('Newpass1!');
        await p1.fill('Newpass1!');
        await p1.blur();
        const resetPost = page.waitForResponse(
            (r) =>
                r.request().method() === 'POST' &&
                /\/(?:api\/)?v1\/auth\/reset-password/.test(r.url())
        );
        await page.getByRole('button', { name: 'إعادة تعيين كلمة المرور' }).click();
        await resetPost;
        await expect(page.getByText('تم إعادة تعيين كلمة المرور بنجاح')).toBeVisible({
            timeout: 15_000,
        });
    });

    test('verify-email page shows resend when session cookie is present', async ({
        page,
        baseURL,
    }) => {
        await page.route(isAppRestApiUrl, async (route) => {
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

        await page.route(isAppRestApiUrl, async (route) => {
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
        await expect(profileNameInput(page)).toHaveValue('Playwright User', {
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

        await page.route(isAppRestApiUrl, async (route) => {
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
        await expect(page.getByRole('heading', { name: 'الملف الشخصي' })).toBeVisible();
        const nameInput = profileNameInput(page);
        await expect(nameInput).toHaveValue('Playwright User', { timeout: 15_000 });
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
