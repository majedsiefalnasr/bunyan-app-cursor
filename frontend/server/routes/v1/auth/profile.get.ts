const E2E_ROLES = [
    'customer',
    'contractor',
    'supervising_architect',
    'field_engineer',
    'admin',
] as const;

type E2ERole = (typeof E2E_ROLES)[number];

function isE2ERole(s: string): s is E2ERole {
    return (E2E_ROLES as readonly string[]).includes(s);
}

/**
 * Same-origin profile stub for Playwright e2e.
 * Browser requests are mocked via `page.route`, but SSR `fetchUser()` runs in Nitro and
 * must hit a real handler — see `middleware/role.ts` and `playwright.config.ts` webServer env.
 */
export default defineEventHandler((event) => {
    if (process.env.PLAYWRIGHT_TEST !== '1') {
        throw createError({ statusCode: 404, statusMessage: 'Not Found' });
    }

    const bearer = getHeader(event, 'authorization')
        ?.replace(/^Bearer\s+/i, '')
        ?.trim();
    const cookieToken = getCookie(event, 'auth_token');
    const token = (bearer || cookieToken || '').trim();

    if (!token) {
        throw createError({ statusCode: 401, statusMessage: 'Unauthorized' });
    }

    const defaultCustomer = {
        id: 42,
        name: 'Playwright User',
        email: 'pw@example.com',
        role: 'customer' as const,
        phone: null as string | null,
        active: true,
        permissions: [] as string[],
        email_verified_at: null as string | null,
        created_at: '2020-01-01T00:00:00.000000Z',
        updated_at: '2020-01-01T00:00:00.000000Z',
    };

    const m = /^e2e-([a-z_]+)$/.exec(token);
    if (!m) {
        return {
            success: true,
            data: defaultCustomer,
            message: null,
            errors: [],
            error: null,
        };
    }

    const key = m[1] ?? '';
    if (isE2ERole(key)) {
        return {
            success: true,
            data: {
                id: 42,
                name: `Playwright ${key}`,
                email: `pw-${key}@example.com`,
                role: key,
                phone: null,
                active: true,
                permissions: [] as string[],
                email_verified_at: null,
                created_at: '2020-01-01T00:00:00.000000Z',
                updated_at: '2020-01-01T00:00:00.000000Z',
            },
            message: null,
            errors: [],
            error: null,
        };
    }

    // e2e-projects, e2e-profile, e2e-cancel, e2e-verify, etc.
    return {
        success: true,
        data: defaultCustomer,
        message: null,
        errors: [],
        error: null,
    };
});
