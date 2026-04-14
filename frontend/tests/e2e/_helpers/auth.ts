import type { Page } from '@playwright/test';

export type E2EUserRole =
    | 'customer'
    | 'contractor'
    | 'supervising_architect'
    | 'field_engineer'
    | 'admin';

function hostFromBase(baseURL: string | undefined) {
    if (!baseURL) return 'localhost';
    try {
        return new URL(baseURL).hostname;
    } catch {
        return 'localhost';
    }
}

function json(body: unknown, status = 200) {
    return {
        status,
        contentType: 'application/json',
        body: JSON.stringify(body),
    };
}

function matchesProfileGet(req: { method: () => string; url: () => string }) {
    return req.method() === 'GET' && /\/v1\/auth\/profile|\/api\/v1\/auth\/profile/.test(req.url());
}

export async function e2eLoginAs(
    page: Page,
    opts: { baseURL?: string; role: E2EUserRole; token?: string; userId?: number }
) {
    const host = hostFromBase(opts.baseURL);
    const token = opts.token ?? `e2e-${opts.role}`;
    const userId = opts.userId ?? 42;

    await page.context().addCookies([
        {
            name: 'auth_token',
            value: token,
            domain: host,
            path: '/',
            sameSite: 'Lax',
        },
    ]);

    await page.route('**/*', async (route) => {
        if (matchesProfileGet(route.request())) {
            await route.fulfill(
                json({
                    success: true,
                    data: {
                        id: userId,
                        name: `Playwright ${opts.role}`,
                        email: `pw-${opts.role}@example.com`,
                        role: opts.role,
                        phone: null,
                        active: true,
                        permissions: [],
                        email_verified_at: null,
                        created_at: '2020-01-01T00:00:00.000000Z',
                        updated_at: '2020-01-01T00:00:00.000000Z',
                    },
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
