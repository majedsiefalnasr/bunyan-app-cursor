import { defineConfig, devices } from '@playwright/test';

const ci = !!process.env.CI;
const isTruthy = (value: string | undefined) =>
    value === '1' || value === 'true' || value === 'yes' || value === 'on';

/**
 * Always use IPv4 loopback for `baseURL` and `nuxt dev --host`.
 * `localhost` can resolve to `::1` while Vite/Nuxt listens on `127.0.0.1` only,
 * which makes Playwright's webServer health check hang until timeout.
 */
const serverHost = process.env.PW_SERVER_HOST || '127.0.0.1';
const serverPort = Number(process.env.PW_SERVER_PORT || '3000');
const baseURL = process.env.PW_BASE_URL || `http://${serverHost}:${serverPort}`;
const devServerCommand =
    process.env.PW_WEB_SERVER_COMMAND || `npm run dev -- --host ${serverHost} --port ${serverPort}`;

const headless = !isTruthy(process.env.PW_HEADED);
const browsers = (process.env.PW_BROWSERS || '').trim().toLowerCase(); // "" | "all"

const defaultBrowserProjects = [{ name: 'chromium', use: { ...devices['Desktop Chrome'] } }];
const allBrowserProjects = [
    { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    { name: 'firefox', use: { ...devices['Desktop Firefox'] } },
];

export default defineConfig({
    testDir: './tests/e2e',

    // One shared `nuxt dev` — parallel workers corrupt HMR / SSR and flake badly.
    fullyParallel: false,
    workers: 1,

    forbidOnly: ci,
    retries: ci ? 2 : 0,
    globalTimeout: ci ? 15 * 60 * 1000 : 0,

    reporter: ci ? [['dot'], ['html', { open: 'never' }]] : [['list'], ['html', { open: 'never' }]],
    use: {
        baseURL,
        headless,
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
    // Fast-by-default: run Chromium only unless explicitly opting into "all".
    // Example: `PW_BROWSERS=all npx playwright test`
    projects: browsers === 'all' ? allBrowserProjects : defaultBrowserProjects,
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
