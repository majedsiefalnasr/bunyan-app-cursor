import { expect, test } from '@playwright/test';

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
});
