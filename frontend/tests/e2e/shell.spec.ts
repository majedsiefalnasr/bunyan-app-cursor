import { expect, test, type Page } from '@playwright/test';

async function gotoArHome(page: Page) {
    await page.goto('/ar/', { waitUntil: 'domcontentloaded' });
    await page.waitForLoadState('networkidle').catch(() => {
        /* dev server may keep sockets open; domcontentloaded + visible shell is enough */
    });
}

test.describe('Application Shell', () => {
    test('shell renders AppHeader and navigation for unauthenticated user', async ({ page }) => {
        await gotoArHome(page);
        await expect(page.locator('header')).toBeVisible();
        await expect(page.getByTestId('rtl-toggle')).toBeVisible();
    });

    test('RTL direction toggle persists across navigation', async ({ page }) => {
        await gotoArHome(page);

        await expect(page.locator('html')).toHaveAttribute('dir', 'rtl');

        await page.getByTestId('rtl-toggle').click();
        await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');

        await gotoArHome(page);
        await expect(page.locator('html')).toHaveAttribute('dir', 'ltr');
    });

    test('dark mode toggle applies dark class to html', async ({ page }) => {
        await gotoArHome(page);

        await page.getByTestId('theme-toggle').click();

        await expect(page.locator('html')).toHaveClass(/dark/);
    });

    test('language switch AR→EN updates page URL', async ({ page }) => {
        await gotoArHome(page);

        await page.getByTestId('language-switcher').click();
        const enOption = page.locator('.e2e-locale-en');
        await expect(enOption).toBeVisible();
        await enOption.click();

        await expect(page).toHaveURL(/\/en(?:\/|$)/);
    });

    test('mobile drawer opens on 375px viewport', async ({ page }) => {
        await page.setViewportSize({ width: 375, height: 812 });
        await page.goto('/ar/');

        const hamburger = page.getByTestId('mobile-nav-toggle');
        await expect(hamburger).toBeVisible();

        await hamburger.click();

        const drawer = page.getByTestId('mobile-drawer');
        await expect(drawer).toBeVisible();
        await expect(page.getByRole('button', { name: /إغلاق|Close/ })).toBeVisible();
    });

    test('navigation items are visible in sidebar on desktop', async ({ page }) => {
        await page.setViewportSize({ width: 1280, height: 800 });
        await page.goto('/ar/');

        const sidebar = page.locator('aside');
        await expect(sidebar).toBeVisible();
    });
});
