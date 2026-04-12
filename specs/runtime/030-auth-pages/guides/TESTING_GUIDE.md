# Testing Guide — STAGE_30 Auth Pages

> **Phase:** 07_FRONTEND_APPLICATION  
> **Generated:** 2026-04-12

## Prerequisites

```bash
cd frontend
npm install
# Optional: copy .env for API base URL
cp .env.example .env
```

## Automated — unit (Vitest)

```bash
cd frontend
npm run test
```

**Focus filters (examples):**

```bash
npm run test -- tests/unit/stores/user.spec.ts
npm run test -- tests/unit/schemas/auth.spec.ts
```

## Automated — E2E (Playwright)

Playwright starts `nuxt dev` via `playwright.config.ts`. If the web server times out, ensure port **3000** is free or increase `webServer.timeout`.

```bash
cd frontend
PLAYWRIGHT_TEST=1 npm run test:e2e -- --project=chromium
```

Run a single file:

```bash
PLAYWRIGHT_TEST=1 npx playwright test tests/e2e/auth.spec.ts --project=chromium
```

## Manual scenario — login (Arabic)

1. Open `http://localhost:3000/ar/auth/login` (with `NUXT_PUBLIC_API_BASE_URL` pointing at a running Laravel API, or use mocked network in Playwright).
2. Enter a valid user email/password.
3. **Expected:** Redirect to `/ar/dashboard`; `auth_token` cookie present (Application → Cookies).

## Manual scenario — profile update

1. While authenticated, open `/ar/profile`.
2. Change **Name** and optional **Phone**; click **حفظ التغييرات**.
3. **Expected:** Green success alert; reload page — values persist.
4. Edit again, click **إلغاء**.
5. **Expected:** Fields revert to last saved server values.

## Manual scenario — forgot / reset password

1. `/ar/auth/forgot-password` — submit email.
2. Use reset link from mail (or construct `/ar/auth/reset-password?token=...&email=...` in staging).
3. Set new password; **Expected:** Success message and redirect to login.

## Manual scenario — register wizard

1. `/ar/auth/register` — complete steps 1–3; confirm step 4 shows email instructions.
2. Optional: click **إعادة إرسال رابط التحقق** when logged in with unverified email.

## RTL / i18n smoke

1. `/ar/auth/login` — placeholders Arabic; `html[dir="rtl"]` default from app config.
2. `/en/auth/login` — placeholders English; URL uses `/en/` prefix.
