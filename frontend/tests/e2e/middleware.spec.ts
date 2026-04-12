import { expect, test } from '@playwright/test';

test.describe('Auth middleware', () => {
    test('unauthenticated visit to profile redirects to login with redirect query', async ({
        page,
    }) => {
        await page.goto('/ar/profile', { waitUntil: 'domcontentloaded' });

        await expect(page).toHaveURL(/\/ar\/auth\/login/);

        const url = new URL(page.url());
        const redirect = url.searchParams.get('redirect');
        expect(redirect).toBeTruthy();
        expect(decodeURIComponent(redirect ?? '')).toContain('/ar/profile');
    });
});
