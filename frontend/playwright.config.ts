import { defineConfig, devices } from '@playwright/test';

const ci = !!process.env.CI;
/** Avoid IPv6 `localhost` → `::1` connection stalls on Linux CI runners. */
const serverHost = ci ? '127.0.0.1' : 'localhost';
const baseURL = `http://${serverHost}:3000`;

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
        command: ci ? 'npm run dev -- --host 127.0.0.1 --port 3000' : 'npm run dev',
        url: baseURL,
        reuseExistingServer: !ci,
        timeout: ci ? 180_000 : 60_000,
        stdout: 'ignore',
        stderr: ci ? 'ignore' : 'pipe',
    },
});
