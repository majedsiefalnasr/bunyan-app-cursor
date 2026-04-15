import { expect, type Page } from '@playwright/test';

/**
 * Nuxt SSR serves HTML first; `UForm` validates `state` on submit. If Playwright fills the DOM
 * before Vue hydrates, the reactive `state` stays empty → Zod fails silently (no `@submit`, no fetch).
 */
export async function gotoAuthForm(page: Page, path: string) {
    await page.goto(path, { waitUntil: 'load' });
    await page.locator('form').first().waitFor({ state: 'visible' });
    await expect(page.locator('form input').first()).toBeVisible({ timeout: 15_000 });
}
