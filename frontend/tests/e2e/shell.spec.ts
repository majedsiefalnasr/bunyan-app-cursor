import { expect, test } from '@playwright/test';

test.describe('Application Shell', () => {
    test('shell renders AppHeader and navigation for unauthenticated user', async ({ page }) => {
        await page.goto('/ar/');
        await expect(page.locator('header')).toBeVisible();
        await expect(page.locator('[data-testid="rtl-toggle"]')).toBeVisible();
    });

    test('RTL direction toggle persists across navigation', async ({ page }) => {
        await page.goto('/ar/');

        await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');

        await page.click('[data-testid="rtl-toggle"]');
        await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');

        await page.goto('/ar/');
        await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
    });

    test('dark mode toggle applies dark class to html', async ({ page }) => {
        await page.goto('/ar/');

        await page.click('[data-testid="theme-toggle"]');

        await expect(page.locator('html')).toHaveClass(/dark/);
    });

    test('language switch AR→EN updates page URL', async ({ page }) => {
        await page.goto('/ar/');

        await page.click('button:has([class*="heroicons-language"])');
        await page.click('text=English');

        await expect(page).toHaveURL(/\/en\//);
    });

    test('mobile drawer opens and closes on 375px viewport', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 812 });
        await page.goto('/ar/');

        const hamburger = page.locator('button[aria-label]').filter({
            has: page.locator('[class*="heroicons-bars-3"]'),
        });
        await expect(hamburger).toBeVisible();

        await hamburger.click();

        const drawer = page.locator('[role="dialog"]');
        await expect(drawer).toBeVisible();

        await page.locator(`button[aria-label="${'إغلاق'}"], button[aria-label="Close"]`).click();
        await expect(drawer).not.toBeVisible();
    });

    test('navigation items are visible in sidebar on desktop', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 800 });
        await page.goto('/ar/');

        const sidebar = page.locator('aside');
        await expect(sidebar).toBeVisible();
    });
});
