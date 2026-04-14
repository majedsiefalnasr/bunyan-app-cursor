import { expect, test } from '@playwright/test';

test.describe('Commercial compatibility routes', () => {
    test('RFQ create compatibility route redirects away from /rfqs/create', async ({ page }) => {
        await page.goto('/ar/rfqs/create', { waitUntil: 'domcontentloaded' });
        await expect(page).not.toHaveURL(/\/rfqs\/create$/);
    });

    test('Checkout compatibility route redirects away from /checkout', async ({ page }) => {
        await page.goto('/ar/checkout', { waitUntil: 'domcontentloaded' });
        await expect(page).not.toHaveURL(/\/checkout$/);
    });
});
