import { defineConfig, devices } from '@playwright/test';

const ci = !!process.env.CI;
/**
 * Always use IPv4 loopback for `baseURL` and `nuxt dev --host`.
 * `localhost` can resolve to `::1` while Vite/Nuxt listens on `127.0.0.1` only,
 * which makes Playwright's webServer health check hang until timeout.
 */
const serverHost = '127.0.0.1';
const baseURL = `http://${serverHost}:3000`;
const devServerCommand = `npm run dev -- --host ${serverHost} --port 3000`;

export default defineConfig({
    testDir: './tests/e2e',
    // One shared `nuxt dev` — parallel workers corrupt HMR / SSR and flake badly.
    fullyParallel: false,
    forbidOnly: ci,
    retries: ci ? 2 : 0,
    workers: 1,
    globalTimeout: ci ? 15 * 60 * 1000 : 0,
    reporter: 'html',
    use: {
        baseURL,
        trace: 'on-first-retry',
    },
    projects: ci
        ? [{ name: 'chromium', use: { ...devices.chromium } }]
        : [
              { name: 'chromium', use: { ...devices.chromium } },
              { name: 'firefox', use: { ...devices.firefox } },
          ],
    webServer: {
        command: devServerCommand,
        url: baseURL,
        reuseExistingServer: !ci,
        /** Cold `nuxt dev` (deps optimize, Nitro) can exceed 60s on slower disks. */
        timeout: ci ? 180_000 : 120_000,
        stdout: 'ignore',
        stderr: ci ? 'ignore' : 'pipe',
        env: {
            ...process.env,
            /** Disables Nuxt DevTools during e2e (see `nuxt.config.ts`). */
            PLAYWRIGHT_TEST: '1',
            /**
             * CI sets `NUXT_PUBLIC_API_BASE_URL` to Laravel on :8000. Client `$fetch` then
             * targets another origin; Playwright route mocks are reliable for same-origin
             * requests to this dev server (e.g. `/v1/auth/profile`), and match local e2e.
             */
            NUXT_PUBLIC_API_BASE_URL: '',
        },
    },
});
