import { expect, test } from '@playwright/test';

import { isAppRestApiUrl } from './_helpers/apiPath';
import { gotoAuthForm } from './_helpers/nuxtReady';

test.describe('Accessibility basics', () => {
    test('login form fields are keyboard reachable', async ({ page }) => {
        await page.goto('/ar/auth/login', { waitUntil: 'domcontentloaded' });
        const email = page.getByRole('textbox', { name: 'البريد الإلكتروني' });
        const pwd = page.getByRole('textbox', { name: 'كلمة المرور' });
        await email.focus();
        await expect(email).toBeFocused();
        await page.keyboard.press('Tab');
        await expect(pwd).toBeFocused();
    });

    test('auth error alert exposes role=alert when present', async ({ page }) => {
        await gotoAuthForm(page, '/ar/auth/login');
        const email = page.getByRole('textbox', { name: 'البريد الإلكتروني' });
        const pwd = page.getByRole('textbox', { name: 'كلمة المرور' });
        await email.fill('x@y.com');
        await pwd.fill('password12');
        await pwd.blur();
        // Mock API only after fields are set — registering a route can remount HMR clients and clear `UForm` state.
        await page.route(isAppRestApiUrl, async (route) => {
            const req = route.request();
            if (
                req.method() === 'POST' &&
                /\/v1\/auth\/login|\/api\/v1\/auth\/login/.test(req.url())
            ) {
                await route.fulfill({
                    status: 422,
                    contentType: 'application/json',
                    body: JSON.stringify({
                        success: false,
                        data: null,
                        message: null,
                        errors: [],
                        error: {
                            code: 'AUTH_INVALID_CREDENTIALS',
                            message: 'بيانات الدخول غير صحيحة',
                            details: null,
                        },
                    }),
                });
                return;
            }
            await route.continue();
        });
        await page.getByRole('button', { name: 'تسجيل الدخول' }).click();
        const alert = page.getByTestId('auth-error-alert');
        await expect(alert).toBeVisible({ timeout: 15_000 });
        await expect(alert).toHaveAttribute('role', 'alert');
    });
});
