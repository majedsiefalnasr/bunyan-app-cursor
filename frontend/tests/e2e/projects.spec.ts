import { expect, test, type Page } from '@playwright/test';

import { isAppRestApiUrl } from './_helpers/apiPath';

const apiProfile = {
    id: 42,
    name: 'Playwright User',
    email: 'pw@example.com',
    role: 'customer' as const,
    phone: null,
    active: true,
    email_verified_at: null,
    created_at: '2020-01-01T00:00:00.000000Z',
    updated_at: '2020-01-01T00:00:00.000000Z',
    permissions: [] as string[],
};

function json(body: unknown, status = 200) {
    return {
        status,
        contentType: 'application/json',
        body: JSON.stringify(body),
    };
}

function hostFromBase(baseURL: string | undefined) {
    if (!baseURL) return 'localhost';
    try {
        return new URL(baseURL).hostname;
    } catch {
        return 'localhost';
    }
}

function matchesProfileGet(req: { method: () => string; url: () => string }) {
    return req.method() === 'GET' && /\/v1\/auth\/profile|\/api\/v1\/auth\/profile/.test(req.url());
}

async function withAuthProfile(page: Page, baseURL: string | undefined) {
    const host = hostFromBase(baseURL);
    await page.context().addCookies([
        {
            name: 'auth_token',
            value: 'e2e-projects',
            domain: host,
            path: '/',
            sameSite: 'Lax',
        },
    ]);

    await page.route(isAppRestApiUrl, async (route) => {
        if (matchesProfileGet(route.request())) {
            await route.fulfill(
                json({
                    success: true,
                    data: apiProfile,
                    message: null,
                    errors: [],
                    error: null,
                })
            );
            return;
        }
        await route.continue();
    });
}

test.describe('Project pages', () => {
    test('unauthenticated visit to /ar/projects redirects to login with redirect query', async ({
        page,
    }) => {
        await page.goto('/ar/projects', { waitUntil: 'domcontentloaded' });
        await expect(page).toHaveURL(/\/ar\/auth\/login/);
        const url = new URL(page.url());
        const redirect = url.searchParams.get('redirect');
        expect(redirect).toBeTruthy();
        expect(decodeURIComponent(redirect ?? '')).toContain('/ar/projects');
    });

    test('project wizard exposes project-name testid when session is present', async ({
        page,
        baseURL,
    }) => {
        await withAuthProfile(page, baseURL);
        await page.goto('/ar/projects/create', { waitUntil: 'domcontentloaded' });
        await expect(page.getByTestId('project-name')).toBeVisible({ timeout: 15_000 });
    });
});
