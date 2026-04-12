import { expect, test } from '@playwright/test';

test.describe('RTL layout', () => {
    test('Arabic login route keeps html dir rtl by default', async ({ page }) => {
        await page.goto('/ar/auth/login', { waitUntil: 'domcontentloaded' });
        await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');
    });

    test('English login route uses /en/ prefix and translated UI', async ({ page }) => {
        await page.goto('/en/auth/login', { waitUntil: 'domcontentloaded' });
        await expect(page).toHaveURL(/\/en\/auth\/login/);
        await expect(page.getByPlaceholder('Enter your email')).toBeVisible();
    });
});
