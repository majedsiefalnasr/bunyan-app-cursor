import { expect, test } from '@playwright/test';

test.describe('i18n', () => {
    test('English login page shows translated placeholders', async ({ page }) => {
        await page.goto('/en/auth/login', { waitUntil: 'domcontentloaded' });
        await expect(page.getByPlaceholder('Enter your email')).toBeVisible();
        await expect(page.getByPlaceholder('Enter your password')).toBeVisible();
    });

    test('Arabic login page shows Arabic placeholders', async ({ page }) => {
        await page.goto('/ar/auth/login', { waitUntil: 'domcontentloaded' });
        await expect(page.getByPlaceholder('أدخل بريدك الإلكتروني')).toBeVisible();
    });
});
