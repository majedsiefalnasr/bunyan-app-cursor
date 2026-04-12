import { expect, test } from '@playwright/test';

test.describe('Accessibility basics', () => {
    test('login form fields are keyboard reachable', async ({ page }) => {
        await page.goto('/ar/auth/login', { waitUntil: 'domcontentloaded' });
        const email = page.getByPlaceholder('أدخل بريدك الإلكتروني');
        const pwd = page.getByPlaceholder('أدخل كلمة المرور');
        await email.focus();
        await expect(email).toBeFocused();
        await page.keyboard.press('Tab');
        await expect(pwd).toBeFocused();
    });

    test('auth error alert exposes role=alert when present', async ({ page }) => {
        await page.route('**/*', async (route) => {
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

        await page.goto('/ar/auth/login', { waitUntil: 'domcontentloaded' });
        await page.getByPlaceholder('أدخل بريدك الإلكتروني').fill('x@y.com');
        await page.getByPlaceholder('أدخل كلمة المرور').fill('password12');
        await page.getByRole('button', { name: 'تسجيل الدخول' }).click();
        await expect(page.getByTestId('auth-error-alert')).toHaveAttribute('role', 'alert', {
            timeout: 15_000,
        });
    });
});
