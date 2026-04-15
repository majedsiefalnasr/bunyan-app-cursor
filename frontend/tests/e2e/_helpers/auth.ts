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

export function matchesE2eProfileGet(req: { method: () => string; url: () => string }) {
    return req.method() === 'GET' && /\/v1\/auth\/profile|\/api\/v1\/auth\/profile/.test(req.url());
}

/** JSON body for `route.fulfill` — reuse in catch‑all `page.route` handlers (they run before this module's route). */
export function e2eProfileFulfill(opts: { role: E2EUserRole; userId?: number }) {
    const userId = opts.userId ?? 42;
    return json({
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
    });
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

    // Dedicated profile route for tests that do not register a catch‑all network mock.
    // When tests use a catch‑all `page.route`, Playwright invokes that handler first — those
    // tests must call `matchesE2eProfileGet` + `e2eProfileFulfill` at the top of the handler.
    await page.route(/\/(?:api\/)?v1\/auth\/profile(?:\?|$)/, async (route) => {
        if (!matchesE2eProfileGet(route.request())) {
            await route.continue();
            return;
        }

        await route.fulfill(e2eProfileFulfill({ role: opts.role, userId }));
    });

    // Establish a document for this origin so the `auth_token` cookie is reliably attached
    // to subsequent navigations (avoids RBAC races on deep-linked admin routes in e2e).
    if (opts.baseURL) {
        const origin = opts.baseURL.replace(/\/$/, '');
        await page.goto(`${origin}/ar/`, { waitUntil: 'load' });
        // Let client-side Pinia + i18n finish hydrating from the cookie-backed profile fetch.
        await page.waitForTimeout(400);
    }
}
