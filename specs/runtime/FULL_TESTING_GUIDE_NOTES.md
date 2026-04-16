# FULL_TESTING_GUIDE — Review Notes (interactive)

This file is generated during an interactive, step-by-step execution of `FULL_TESTING_GUIDE.md`.

## How we use this file

- After each step, you review results.
- If you request changes/clarifications, we record them here as notes (and optionally update the guide later).

## Session log

### Step 0 — Initialized notes file

- Status: created

### Step 1 — Prerequisites check (versions)

- Node: `v23.11.0` (guide recommends 20 LTS)
- npm: `11.3.0`
- PHP: `8.5.4` (guide says 8.2+ and mentions backend platform pinned to 8.2.x)
- Composer: `2.9.5`
- Docker: `29.3.1`
- Repo root verified: `/Users/majedsiefalnasr/Documents/Work/MAJED/bunyan-app-cursor` (contains `backend/`, `frontend/`, `package.json`)

Notes:

- Potential risk: Composer `platform` constraints or lockfiles may expect PHP 8.2.x; Node toolchain may assume Node 20.

### Step 2 — Install dependencies (`npm run install`)

- Result: ✅ succeeded (exit code 0) using `rtk proxy npm run install` (plain `rtk npm run ...` appears broken for npm in this environment)
- Backend (`composer install`): ✅ no changes needed, autoload + package discover ran
- Frontend (`npm install`): ✅ completed, but with warnings:
  - Peer dependency override warning involving `vite-plugin-vue-tracer@1.3.0` expecting Vite `^6 || ^7` while repo has `vite@8.0.8`
  - Multiple `EBADENGINE Unsupported engine` warnings: packages require Node `^20.19.0 || ^22.13.0 || >=24`, current Node is `v23.11.0`
  - `npm audit` summary (initially): 3 vulnerabilities (1 moderate, 2 critical) reported

Step 2 follow-up fixes applied:

- Fixed `happy-dom` critical advisories without upgrading Vitest:
  - Updated direct dev dependency `happy-dom` to `20.9.0`
  - Added npm `overrides` to force `@nuxt/test-utils` to use `happy-dom@20.9.0`
- Fixed `follow-redirects` advisory:
  - Added npm `overrides` to force `follow-redirects@1.16.0` (pulled via `axios`)
- Re-ran `npm install` + `npm audit`
  - ✅ `npm audit` now reports: **0 vulnerabilities**

Remaining Step 2 warnings (not fixed yet):

- `EBADENGINE` warnings because Node is `v23.11.0`

Reviewer decision (Majed):

- Node: update local Node to **>= 24** to satisfy tooling engines (`^20.19 || ^22.13 || >=24`)
- Vite peer mismatch: update `@nuxt/devtools` to a version that supports Vite 8

Reality check (current ecosystem state):

- `@nuxt/devtools@latest` is `4.0.0-alpha.4` and still depends on `vite-plugin-vue-tracer@1.3.0`
- `vite-plugin-vue-tracer` latest (`1.3.0`) only supports Vite `^6 || ^7` (no Vite 8 support yet)

Action taken:

- Implemented the alternative approach (per reviewer request): **downgrade Vite to v7** instead of removing devtools
  - Pinned `vite` to `7.3.2`
  - Kept devtools enabled via `nuxt.config.ts` (disabled only in CI/Playwright)
  - Did **not** use `@nuxt/devtools@4.0.0-alpha.4` because it pulls `vite-plugin-inspect@12` which expects Vite 8
  - Resulting devtools: `@nuxt/devtools@3.2.4` (brought by Nuxt) with `vite-plugin-vue-tracer@1.3.0` (compatible with Vite 7)
- Verification:
  - `npm install` ✅
  - `npx nuxi prepare` ✅
  - `npm audit` ✅ 0 vulnerabilities

### Step 3 — Env files

- `.env`: already exists
- `backend/.env`: already exists
- `frontend/.env`: already exists
  Notes:
- We did not overwrite env files (guide suggests copying from examples, but files were present already).

### Step 4 — Docker Compose (MySQL + Redis)

- Attempted: `npm run docker:up` (runs `docker-compose up -d`)
- Result: ❌ failed initially: Docker daemon not reachable (`Cannot connect to the Docker daemon ... docker.sock. Is the docker daemon running?`)
- `docker ps` currently shows 0 containers

Follow-up:

- Docker daemon later became reachable, but `docker:up` failed while building `bunyan-php`:
  - Composer inside the image failed because `ext-gd` was missing (required by `phpoffice/phpspreadsheet` → `maatwebsite/excel`)
  - Fix applied: updated `Dockerfile.backend` to install and enable `gd`

Result after fix:

- ✅ `npm run docker:up` succeeded
- ✅ Containers running: `bunyan-mysql` (healthy), `bunyan-redis` (healthy), `bunyan-php`, `bunyan-node`

### Step 5 — Backend setup: generate app key

- `backend/.env` DB config is set for running artisan on host:
  - `DB_HOST=127.0.0.1`, `DB_DATABASE=bunyan`, `DB_USERNAME=bunyan`, `DB_PASSWORD=bunyan`
- Ran: `cd backend && php artisan key:generate`
- Result: ✅ Application key set successfully

### Step 6 — Backend setup: migrate + seed

- Ran: `cd backend && php artisan migrate:fresh --seed`
- Result: ✅ succeeded (exit code 0)
- Confirmed: `personal_access_tokens` migration ran (Sanctum)

### Step 7 — Backend serve + health check

- Started backend server: `php artisan serve --host=127.0.0.1 --port=8000`
- Initial `GET /` returned 500 due to missing view `welcome` referenced by `routes/web.php`
- Fix applied: changed `GET /` to return a JSON Bunyan envelope
- Current status: `GET http://127.0.0.1:8000` returns **200**

Env fix applied:

- Updated `backend/.env` to use `CACHE_STORE=redis` (Laravel 11) instead of `CACHE_DRIVER`

### Step 8 — Frontend setup + dev server

- Verified `frontend/.env`:
  - `NUXT_PUBLIC_API_BASE_URL=http://localhost:8000`
- Ran: `cd frontend && npx nuxi prepare` ✅
- Started: `cd frontend && npm run dev` on `http://127.0.0.1:3000`
- Behavior:
  - `GET /` returns `302` → redirects to `/ar`
  - `GET /ar` returns `200`
- Note:
  - Node prints an ExperimentalWarning about “Type Stripping” under Node `v23.11.0` (harmless warning; will likely disappear on Node >=24 or supported LTS).

### Manual testing — seeded accounts

Seeded users created by `Database\\Seeders\\UserSeeder` (password for all: `password`)

- Customer (العميل): `customer@example.com`
- Contractor (المقاول): `contractor@example.com`
- Supervising Architect (المهندس المشرف): `architect@example.com`
- Field Engineer (المهندس الميداني): `engineer@example.com`
- Admin (الإدارة): `admin@example.com`

### Auth bugfix — "Unexpected error" on login/register

- Symptom: UI shows `حدث خطأ غير متوقع` on sign-in and sign-up
- Root cause: backend used Redis-based rate limiting / cache store but host PHP lacked the `redis` extension → `Class "Redis" not found` (500)
- Fix applied (local dev): updated `backend/.env`
  - `CACHE_STORE=file` (instead of redis)
  - `QUEUE_CONNECTION=sync` (instead of redis)
- Verification:
  - `POST /api/v1/auth/login` now returns 200 with token for `customer@example.com`
  - `POST /api/v1/auth/register` now returns validation errors (expected) for weak passwords

### Frontend auth still failing — root cause and fix

- Cause: Nuxt runtime config `public.apiBaseUrl` was empty at runtime, so frontend was calling `/v1/auth/login` on `:3000` instead of the backend on `:8000` (resulting in generic "unexpected error").
- Fix:
  - Set `runtimeConfig.public.apiBaseUrl` default from `process.env.NUXT_PUBLIC_API_BASE_URL` in `frontend/nuxt.config.ts`
  - Restarted `nuxt dev` with explicit `NUXT_PUBLIC_API_BASE_URL=http://127.0.0.1:8000`

Follow-up:

- Discovered mismatch: frontend auth calls `/v1/...` while backend routes are under `/api/v1/...`
- Fix applied: normalized API base URL in `frontend/composables/useApi.ts`:
  - If `apiBaseUrl` is `http://127.0.0.1:8000`, client uses `http://127.0.0.1:8000/api` as fetch base.

### Frontend login error — Vue I18n composable misuse

- Symptom: login throws `Must be called at the top of a setup function`
- Root cause: `useI18n()` was being called inside `useErrorNotification()` (invoked from `useApi()` during store actions), which violates Vue I18n composable rules.
- Fix applied: updated `frontend/composables/useErrorNotification.ts` to use Nuxt-injected i18n instance (`useNuxtApp().$i18n`) instead of `useI18n()`.

Cleanup:

- Removed temporary login page debug UI (apiBaseUrl/computedBase + error JSON)
- Deleted temporary Nuxt debug endpoints under `frontend/server/api/_debug/*`

### i18n fix — auth error message language

- Issue: wrong-password login showed mixed English + Arabic
- Root cause: login page error handler displayed backend `error.message` directly (English) instead of preferring localized `errors.codes.*` message.
- Fix: updated `frontend/pages/auth/login.vue` to prefer `errors.codes.${error.code}.message` when available.

Follow-up:

- Toast title was showing the raw error code (e.g. `AUTH_INVALID_CREDENTIALS`).
- Fix: updated `frontend/composables/useErrorNotification.ts` so toast title uses the localized message; code is no longer shown in the toast UI.

### Story B — RBAC (Customer vs Admin)

- Customer UI:
  - Visiting `/ar/admin` and `/ar/admin/users` redirects to `/ar/dashboard` ✅
- Admin UI:
  - Admin can access `/ar/admin` and `/ar/admin/users` ✅
- Server-side:
  - Customer calling `GET /api/v1/admin/users` returns 403 `RBAC_ROLE_DENIED` ✅

### Story C — Phase creation UI

- Issue: customer could not create phases from UI (project page showed phases empty with no add controls).
- Fix: added phases list + "Add phase" modal to `frontend/pages/projects/[id]/index.vue` using backend endpoints:
  - `GET /api/v1/projects/{project}/phases`
  - `POST /api/v1/projects/{project}/phases`
- Added i18n keys in `frontend/locales/ar.json` and `frontend/locales/en.json`.

Follow-up fixes:

- Added `common.save` and `common.cancel` translation keys (used by the new modal).
- Backend RBAC fix: phase creation routes were incorrectly restricted to `role:contractor,admin` only.
  - Updated `backend/routes/api.php` to allow `role:customer,contractor,admin` for phase create/update routes.
- Backend policy fix: `PhasePolicy::create` now accepts `(User $user, Project $project)` to match controller authorization call.

### Story D — Contractor sees assigned projects

- Issue: contractor projects list was empty because project had no contractor assigned.
- Fix: enabled admin-only assignment fields in project update:
  - Backend: `UpdateProjectRequest` now supports `contractor_id` and `supervising_architect_id` (admin-only)
  - Backend: `ProjectService::updateProject` now allows updating those fields
- Verified: contractor now sees assigned project in `GET /api/v1/projects`

### Story D — Contractor allowed vs forbidden actions

- UI:
  - Contractor can add a phase ✅
  - No delete UI exposed ✅
- Server-side:
  - Contractor attempting `DELETE /api/v1/projects/1/phases/1` returns 403 `RBAC_ROLE_DENIED` ✅

### Story E — Supervising Architect sees supervised projects

- Issue: supervising architect project list was empty because project had no supervising architect assigned.
- Action: admin assigned `supervising_architect_id=3` to project `1`.
- Verified: supervising architect now sees the project in `GET /api/v1/projects`.

### Story F — Field Engineer project visibility

- Issue: field engineer projects list was empty even after being added to project team.
- Root cause: `Project::scopeForUser()` only included projects where the field engineer had authored reports.
- Fix: updated `Project::scopeForUser()` to include projects where the field engineer is a `project_members` member (or has authored reports).
- Verified: field engineer now sees the project in `GET /api/v1/projects`.

Follow-up:

- Field engineer attempted to create a phase and got 403 (expected).
- Fix: gated the "Add phase" UI in `frontend/pages/projects/[id]/index.vue` to only show for `customer|contractor|admin`.

### UX fix — protected pages must redirect when unauthenticated

- Issue: opening a protected project page while logged out could still show partial project content and a minimal sidebar.
- Root cause: `frontend/middleware/auth.ts` relied on Pinia store token which may not be hydrated on SSR/first load.
- Fix: auth middleware now reads the `auth_token` cookie directly via `useCookie` and redirects immediately to:
  - `/ar/auth/login?redirect=<original>`

### UX fix — sidebar links on deep links after login

- Issue: when opening a deep link directly (example: `/ar/projects/1/reports`) the app initially showed only the "Home" link in the sidebar, until a normal in-app navigation happened.
- Root cause: role-based navigation was computed from `auth.user.role`, but on hard refresh/deep link the token cookie could exist while `auth.user` was not hydrated yet (so role is `null` at render time).
- Fix:
  - `frontend/pages/auth/login.vue`: honor `?redirect=/...` after successful login (instead of always going to `/dashboard`).
  - `frontend/plugins/auth-init.ts`: on app boot, if a token exists and `auth.user` is null, call `auth.fetchUser()` to hydrate the profile early so sidebar renders correct links.

### Automated tests — `npm run validate`

- Result: ✅ passes (lint + format check + typecheck + PHPStan + backend tests + frontend tests).
- Notes:
  - Backend tests print many deprecation warnings related to `PDO::MYSQL_ATTR_SSL_CA` under PHP 8.5.x, but the suite exits 0.

### Story F — Field Engineer: reports (API smoke)

- API check (field engineer token):
  - Login: ✅
  - Create report (project_id=1): ✅ (created id `3`)
  - List reports with `project_id=1`: ✅ (count: `3`)

### Story G — Workflow transitions (API setup + start)

- Found: starting workflow for a project fails with 422 unless a workflow configuration exists.
- Admin created a project-specific workflow configuration for `project_id=1`:
  - `POST /api/v1/workflows` ✅ (created config id `2`)
- Admin started the project workflow:
  - `POST /api/v1/projects/1/workflow/start` ✅ (instance status: `completed` because there are no approval rules)

### E2E (Playwright) — `npm run test:e2e`

- One-time setup: `npx playwright install` ✅
- Run: `cd frontend && npm run test:e2e` ✅
  - Result: **32 passed**
  - Notes:
    - If a local `nuxt dev` is already running on `:3000`, Playwright may reuse it without the correct `webServer.env` (e.g. `PLAYWRIGHT_TEST=1`) and tests can fail. Best practice: stop the existing server before running E2E so Playwright starts its own.
