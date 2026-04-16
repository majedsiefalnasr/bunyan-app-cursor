# Bunyan (بنيان) — Full Human Testing Guide (Install → DB → Features)

**Audience:** QA / PM / Engineers running end-to-end verification locally (or in a clean environment).  
**Goal:** Confirm the app works from a fresh clone through core user stories (projects + workflow + reports + e-commerce + admin) with **explicit checklists**.

> Source of truth for commands/scripts: root `package.json`, `backend/composer.json`, `frontend/package.json`, and `.github/workflows/*.yml`.

---

## Table of Contents

1. [Quick Start (fast path)](#quick-start-fast-path)
2. [Prerequisites](#prerequisites)
3. [Install the project](#install-the-project)
4. [Database & Redis setup](#database--redis-setup)
5. [Backend setup (Laravel)](#backend-setup-laravel)
6. [Frontend setup (Nuxt)](#frontend-setup-nuxt)
7. [Run the application (local)](#run-the-application-local)
8. [Automated tests](#automated-tests)
9. [Seeded test accounts (recommended)](#seeded-test-accounts-recommended)
10. [Manual test plan — user stories & checklists](#manual-test-plan--user-stories--checklists)
11. [E2E (Playwright) smoke checklist](#e2e-playwright-smoke-checklist)
12. [Troubleshooting](#troubleshooting)
13. [Release/acceptance checklist (copy/paste)](#releaseacceptance-checklist-copypaste)

---

## Quick Start (fast path)

If you already have Node/PHP/Composer installed and want the quickest end-to-end run:

```bash
# 1) Install dependencies
npm run install

# 2) Copy env files
cp .env.example .env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# 3) Start DB/Redis
npm run docker:up

# 4) Backend key + migrate + seed
cd backend
php artisan key:generate
php artisan migrate:fresh --seed

# 5) Run both apps
cd ..
npm run dev
```

**Checklist**

- [ ] `npm run install` succeeds (no dependency resolution failure)
- [ ] `npm run docker:up` shows MySQL + Redis running
- [ ] Backend migrations + seeding succeed (exit code 0)
- [ ] Backend reachable at `http://localhost:8000`
- [ ] Frontend reachable at `http://localhost:3000`

---

## Prerequisites

### Local tools

- **Node.js**: 20 LTS recommended
- **npm**: comes with Node
- **PHP**: 8.2+ (backend composer platform is pinned to PHP 8.2.x)
- **Composer**: 2.6+
- **Docker Desktop** (recommended) for MySQL + Redis

**Checklist**

- [ ] `node --version` prints v20.x
- [ ] `npm --version` prints a version
- [ ] `php --version` prints 8.2+
- [ ] `composer --version` prints 2.6+
- [ ] `docker --version` works (optional but recommended)

---

## Install the project

### Step 1 — Clone

```bash
git clone <repo-url> bunyan-app
cd bunyan-app
```

**Checklist**

- [ ] Repo clones successfully
- [ ] You are in the repo root (contains `backend/`, `frontend/`, `package.json`)

### Step 2 — Install dependencies (recommended way)

```bash
npm run install
```

This runs:

- Backend: `cd backend && composer install`
- Frontend: `cd frontend && npm install`

**Checklist**

- [ ] Backend `vendor/` exists after install
- [ ] Frontend `frontend/node_modules/` exists after install

### Step 3 — Create `.env` files

```bash
cp .env.example .env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

**Checklist**

- [ ] `.env` exists in repo root
- [ ] `backend/.env` exists
- [ ] `frontend/.env` exists

---

## Database & Redis setup

You have two valid paths:

### Option A (recommended) — Docker Compose (MySQL + Redis)

```bash
npm run docker:up
```

Docker will expose:

- MySQL: `127.0.0.1:3306` (db `bunyan`, user `bunyan`, pass `bunyan`, root pass `root`)
- Redis: `127.0.0.1:6379`

**Checklist**

- [ ] `docker ps` shows `bunyan-mysql` and `bunyan-redis` running
- [ ] MySQL accepts connections on port 3306
- [ ] Redis answers `PING` (if you test via `redis-cli`)

### Option B — No Docker (advanced)

- Use local MySQL + Redis and ensure `backend/.env` matches your host settings.
- For **automated backend tests**, MySQL is usually **not needed** if tests use SQLite in-memory (see `backend/phpunit.xml`).

**Checklist**

- [ ] Your local MySQL has a database named `bunyan` (or you updated `DB_DATABASE`)
- [ ] Your local Redis is reachable (or you updated cache/session/queue drivers)

---

## Backend setup (Laravel)

### Step 1 — Generate app key

```bash
cd backend
php artisan key:generate
```

**Checklist**

- [ ] `backend/.env` contains a non-empty `APP_KEY=...`

### Step 2 — Configure DB host correctly

If you run artisan on your **host** (most common), keep:

- `DB_HOST=127.0.0.1`

If you run artisan **inside the `php` container**, use:

- `DB_HOST=mysql`

**Checklist**

- [ ] `DB_HOST` matches your execution environment (host vs container)

### Step 3 — Run migrations + seed

Recommended for a clean local testing dataset:

```bash
php artisan migrate:fresh --seed
```

**Checklist**

- [ ] Command succeeds (exit code 0)
- [ ] No SQL errors in terminal output
- [ ] `personal_access_tokens` exists (Sanctum)

### Step 4 — Start backend

```bash
php artisan serve
```

Backend default: `http://localhost:8000`

**Checklist**

- [ ] Visiting `http://localhost:8000` returns a response (often 404 is fine)
- [ ] API base responds (example): `GET /api/v1/*` routes return JSON envelopes

---

## Frontend setup (Nuxt)

### Step 1 — Install deps (if not already)

```bash
cd ../frontend
npm install
```

### Step 2 — Verify frontend API base URL

`frontend/.env` must include:

- `NUXT_PUBLIC_API_BASE_URL=http://localhost:8000`

**Checklist**

- [ ] `frontend/.env` exists
- [ ] `NUXT_PUBLIC_API_BASE_URL` points to your backend

### Step 3 — Prepare Nuxt types (recommended before first typecheck / IDE)

```bash
npx nuxi prepare
```

**Checklist**

- [ ] `frontend/.nuxt/tsconfig.json` exists after running

### Step 4 — Start frontend

```bash
npm run dev
```

Frontend default: `http://localhost:3000`

**Checklist**

- [ ] Visiting `http://localhost:3000` renders the app shell
- [ ] No blocking errors in browser console on first load

---

## Run the application (local)

From repo root (in two terminals), or use the combined command:

```bash
npm run dev
```

**Checklist**

- [ ] Backend running on `:8000`
- [ ] Frontend running on `:3000`
- [ ] Frontend can call backend (no CORS/network errors; auth flows work)

---

## Automated tests

### Full local validation (recommended)

```bash
npm run validate
```

This runs lint + format check + typecheck + PHPStan + backend tests + frontend tests.

**Checklist**

- [ ] `npm run lint` succeeds
- [ ] `npm run format:check` succeeds
- [ ] `npm run typecheck` succeeds
- [ ] `npm run analyze` succeeds (PHPStan)
- [ ] `npm run test` succeeds (backend + frontend)

### Backend tests only

```bash
cd backend
php artisan test
composer run lint
composer run analyze
```

**Checklist**

- [ ] `php artisan test` exits 0
- [ ] Pint (`composer run lint`) exits 0
- [ ] PHPStan (`composer run analyze`) exits 0

### Frontend tests only

```bash
cd frontend
npx nuxi prepare
npm run lint
npm run typecheck
npm run test
```

**Checklist**

- [ ] ESLint exits 0
- [ ] Typecheck exits 0
- [ ] Vitest exits 0

---

## Seeded test accounts (recommended)

This project’s manual testing is easiest when seeders create users for each role:

- **Customer (العميل)**
- **Contractor (المقاول)**
- **Supervising Architect (المهندس المشرف)**
- **Field Engineer (المهندس الميداني)**
- **Admin (الإدارة)**

If your current seeders do **not** create these yet, you can still test by registering accounts through the UI, then assigning roles via admin (or DB).

**Checklist**

- [ ] You have at least one user for each role (5 total)
- [ ] You can log in with each user

---

## Manual test plan — user stories & checklists

### Common expectations (applies to all scenarios)

- API errors follow the Bunyan envelope:
  - success responses: `{ "success": true, "data": ..., "message": "...", "errors": {} }`
  - error responses: `{ "success": false, "data": null, "message": "...", "errors": { ... } }`
- RBAC is enforced **server-side** (UI hiding is not enough).
- Arabic-first UX: RTL, Arabic labels, and sensible localization.

**Checklist**

- [ ] Unauthenticated API calls to protected endpoints return **401**
- [ ] Authenticated-but-forbidden calls return **403**
- [ ] Sensitive admin data is not returned to non-admin users
- [ ] Arabic pages render RTL correctly (no broken layout)

---

### Story A — Authentication (Register → Login → Logout)

**Roles:** Any

**Steps**

1. Open frontend.
2. Register a new user (or use seeded accounts).
3. Login with correct credentials.
4. Logout.
5. Attempt login with wrong password.

**Checklist**

- [ ] Registration validates required fields and shows errors clearly
- [ ] Duplicate email is rejected (422) with helpful message
- [ ] Login succeeds and redirects to the correct dashboard
- [ ] Logout clears session and blocks protected pages
- [ ] Wrong password shows an error and does not log in

---

### Story B — RBAC: route protection and API protection

**Roles:** Customer + Admin

**Steps**

1. Login as Customer.
2. Try visiting admin routes (e.g. `/ar/admin`, `/ar/admin/users`).
3. Observe redirect and/or toast.
4. In the browser network tab, confirm API returns 403 for admin endpoints.
5. Login as Admin and visit the same pages.

**Checklist**

- [ ] Customer is redirected away from admin pages
- [ ] Customer cannot fetch admin API endpoints (403)
- [ ] Admin can access admin pages and endpoints (200)
- [ ] No role bypass by typing URL directly

---

### Story C — Customer: create a project (مشروع) and add phases (مراحل)

**Roles:** Customer

**Steps**

1. Login as Customer.
2. Create a new project.
3. Add at least 2 phases with budgets and dates.
4. Refresh the page and verify persistence.

**Checklist**

- [ ] Project appears in customer project list after creation
- [ ] Phase budget validation works (positive number, currency formatting OK)
- [ ] Date validation works (end >= start)
- [ ] Refresh keeps data (backend persistence)
- [ ] API responses use the standard envelope

---

### Story D — Contractor: view assigned projects and update execution progress

**Roles:** Contractor

**Steps**

1. Login as Contractor.
2. Open assigned projects list.
3. Open a project and view phases/tasks (as available in your current build).
4. Attempt actions that should be allowed for contractor (per RBAC).

**Checklist**

- [ ] Contractor sees only assigned projects (not all projects)
- [ ] Forbidden actions are blocked server-side (403)
- [ ] Allowed actions succeed and persist

---

### Story E — Supervising Architect: oversight + approvals

**Roles:** Supervising Architect

**Steps**

1. Login as Supervising Architect.
2. Open supervised projects.
3. Review phase/task status and attempt an approval action (if enabled).

**Checklist**

- [ ] Supervising Architect dashboard loads without errors
- [ ] Only supervised projects are visible
- [ ] Approval action (if present) succeeds and records state change
- [ ] Invalid workflow transitions are rejected with a clear error

---

### Story F — Field Engineer: submit field reports (تقارير) with media

**Roles:** Field Engineer

**Steps**

1. Login as Field Engineer.
2. Open assigned tasks/phases.
3. Submit a report with text.
4. Upload an image (and optionally a video).
5. Verify the report appears in the project timeline/report list.

**Checklist**

- [ ] Report text validation works
- [ ] File upload validates type/size (server-side)
- [ ] Uploaded media renders (thumbnail/link) after save
- [ ] Report shows correct author + timestamp

---

### Story G — Workflow transitions (status changes)

**Roles:** Admin / Supervising Architect (depending on RBAC)

**Steps**

1. Open a project with phases/tasks.
2. Perform a valid status transition (e.g. Pending → In Progress).
3. Attempt an invalid transition (e.g. In Progress → Pending) if disallowed.

**Checklist**

- [ ] Valid transitions succeed
- [ ] Invalid transitions fail with a clear message (and correct HTTP status)
- [ ] UI updates match backend state after refresh

---

### Story H — Admin: manage users and roles (إدارة المستخدمين والأدوار)

**Roles:** Admin

**Steps**

1. Login as Admin.
2. Open admin users list.
3. Assign a different role to a user.
4. Login as that user and confirm their dashboard changes.

**Checklist**

- [ ] Admin users list loads (no 500s)
- [ ] Role assignment succeeds and persists
- [ ] Role change is reflected on next login / refresh
- [ ] Non-admin cannot perform role assignment (403)

---

### Story I — E-commerce: browse products → add to cart → place order

**Roles:** Customer

**Steps**

1. Open catalog/shop pages.
2. Browse product list and open a product detail.
3. Add product to cart.
4. Update quantity / remove item.
5. Checkout and place an order (test mode / no real payment).
6. Verify order appears in “My Orders”.

**Checklist**

- [ ] Product list loads and paginates (if applicable)
- [ ] Product detail loads correctly
- [ ] Cart persists across refresh (if implemented)
- [ ] Checkout validates address/contact fields (if implemented)
- [ ] Order is created and visible in orders list
- [ ] Order totals match line items

---

### Story J — Payments (if enabled): create a transaction record safely

**Roles:** Customer + Admin (view)

**Steps**

1. As Customer, initiate a payment flow (if present).
2. Confirm a transaction record is created.
3. As Admin, view transactions/audit log (if present).

**Checklist**

- [ ] Payment form validates amount bounds
- [ ] Transaction is created atomically (no partial state)
- [ ] Sensitive payment secrets are not exposed in UI or logs

---

## E2E (Playwright) smoke checklist

### One-time setup (per machine)

```bash
cd frontend
npx playwright install
```

### Run E2E

```bash
cd frontend
npm run test:e2e
```

**Checklist**

- [ ] E2E suite exits 0
- [ ] Any failures are reproducible with `npm run test:e2e:ui`
- [ ] No flaky tests across 2 consecutive runs (optional but recommended)

---

## Troubleshooting

### Backend can’t connect to MySQL

- If MySQL runs in Docker and artisan runs on host:
  - `DB_HOST=127.0.0.1`
  - `DB_USERNAME=bunyan`
  - `DB_PASSWORD=bunyan`
- If artisan runs inside the `php` container:
  - `DB_HOST=mysql`

**Checklist**

- [ ] MySQL container healthy (compose healthcheck passes)
- [ ] Credentials match `docker-compose.yml`

### Frontend can’t reach backend (401/CSRF/Sanctum issues)

Make sure:

- `frontend/.env` sets `NUXT_PUBLIC_API_BASE_URL=http://localhost:8000`
- `backend/.env` includes `SANCTUM_STATEFUL_DOMAINS=localhost:3000,127.0.0.1:3000`
- `backend/.env` includes `SESSION_DOMAIN=localhost`

**Checklist**

- [ ] Network tab shows requests going to the expected backend origin
- [ ] Cookies/session behavior matches your auth implementation

### Port already in use (3000/8000/3306/6379)

**Checklist**

- [ ] Stop the conflicting process or adjust ports in `docker-compose.yml`

---

## Release/acceptance checklist (copy/paste)

### Environment

- [ ] Repo cloned and dependencies installed (`npm run install`)
- [ ] Env files present (`.env`, `backend/.env`, `frontend/.env`)
- [ ] MySQL + Redis running (recommended: `npm run docker:up`)
- [ ] Backend migrated + seeded (`php artisan migrate:fresh --seed`)
- [ ] Backend running (`http://localhost:8000`)
- [ ] Frontend running (`http://localhost:3000`)

### Automated gates

- [ ] `npm run validate` passes locally
- [ ] `npm run test:e2e` passes (when E2E specs exist)

### Manual user stories (minimum)

- [ ] Auth: register/login/logout
- [ ] RBAC: customer blocked from admin, admin allowed
- [ ] Projects: customer can create project + phases
- [ ] Workflow: at least one valid status transition + invalid transition rejection
- [ ] Reports: field engineer can submit report with media
- [ ] Admin: assign role and verify effect
- [ ] E-commerce: browse → cart → order (if enabled in current build)
