import { expect, type Page } from '@playwright/test';

/**
 * Nuxt SSR serves HTML first; `UForm` validates `state` on submit. If Playwright fills the DOM
 * before Vue hydrates, the reactive `state` stays empty → Zod fails silently (no `@submit`, no fetch).
 */
export async function gotoAuthForm(page: Page, path: string) {
    await page.goto(path, { waitUntil: 'load' });
    // Reduce motion to avoid flakiness in CI/headless transitions (Nuxt UI slideovers, toasts, etc.).
    await page.emulateMedia({ reducedMotion: 'reduce' });
    // In CI, hydration can lag behind the initial `load` event. Wait for a deterministic marker
    // set by `app.vue` when Playwright starts the dev server with `PLAYWRIGHT_TEST=1`.
    await expect(page.locator('html')).toHaveAttribute('data-pw-hydrated', '1', {
        timeout: 15_000,
    });
    await page.locator('form').first().waitFor({ state: 'visible' });
    await expect(page.locator('form input').first()).toBeVisible({ timeout: 15_000 });
}
