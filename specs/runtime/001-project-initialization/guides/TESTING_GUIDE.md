# STAGE_01 Testing Guide — Local Development & Validation

**Status:** COMPLETE  
**Date:** 2026-04-10  
**Scope:** Backend (Laravel), Frontend (Nuxt.js), Docker, CI/CD, E2E

**Implementation alignment:** This document matches the repository layout and scripts as of **2026-04-10** (Laravel 11, Sanctum, Nuxt 3, `@pinia/nuxt`, PHPUnit 11, Vitest 3, ESLint 9 flat config). When commands or counts drift, prefer **`backend/composer.json`**, **`frontend/package.json`**, and **`.github/workflows/*.yml`** as the source of truth.

---

## Table of Contents

1. [Local Development Setup](#local-development-setup)
2. [Running Backend Tests](#running-backend-tests)
3. [Running Frontend Tests](#running-frontend-tests)
4. [Running E2E Tests](#running-e2e-tests)
5. [Manual Testing Scenarios](#manual-testing-scenarios)
6. [Verification Checklist](#verification-checklist)
7. [Troubleshooting](#troubleshooting)

---

## Local Development Setup

### Prerequisites

- **Node.js:** 20 LTS or higher
- **PHP:** 8.2 or higher (platform **8.5** may log PDO/MySQL constant deprecations from **vendor** Laravel config during bootstrap; app `backend/config/database.php` mitigates for the app config. PHPUnit lowers deprecation noise for test runs—see [Running Backend Tests](#running-backend-tests).)
- **Composer:** 2.6 or higher
- **Docker:** 20.10 or higher (optional, for containerized testing)
- **Docker Compose:** 3.8 or higher (optional)

### Step 1: Clone & Install Dependencies

```bash
# Clone the repository
git clone <repository-url> bunyan-app
cd bunyan-app

# Install root npm dependencies
npm run install

# Copy environment files
cp .env.example .env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

### Step 2: Backend Setup

```bash
cd backend

# Install composer dependencies
composer install

# Generate Laravel application key
php artisan key:generate

# (Optional) Create SQLite database for testing
touch database/database.sqlite

# Run migrations
php artisan migrate --database=sqlite

# (Optional) Seed sample data
php artisan db:seed

# Verify backend is running
php artisan serve
# Backend accessible at: http://localhost:8000
```

### Step 3: Frontend Setup

```bash
cd frontend

# Install npm dependencies
npm install

# Generate .nuxt (types + tsconfig) — required before typecheck / first IDE run
npx nuxi prepare

# Start development server
npm run dev
# Frontend accessible at: http://localhost:3000
```

### Step 4: Docker Setup (Optional)

```bash
# From project root
docker-compose up -d

# Verify services running
docker-compose ps

# View logs
docker-compose logs -f

# Services accessible at:
# - MySQL: localhost:3306 (user: root, password: root)
# - Redis: localhost:6379
# - Backend: http://localhost:8000
# - Frontend: http://localhost:3000
```

### Step 5: Pre-Commit Hooks

```bash
# Install Husky hooks
npx husky install

# Pre-commit validation will now run automatically on git commit
```

---

## Running Backend Tests

### Quick reference (this repository)

| Goal | Command |
|------|---------|
| Run all automated tests | `cd backend && composer test` or `cd backend && php artisan test` |
| Feature / API tests only | `cd backend && php artisan test tests/Feature` |
| PHP style (dry-run) | `cd backend && composer run lint` |
| PHP style (apply fixes) | `cd backend && composer run lint:fix` |
| PHPStan | `cd backend && composer run analyze` |
| PHPUnit directly (debug) | `cd backend && ./vendor/bin/phpunit` |

**Database for tests:** `backend/phpunit.xml` sets **`DB_CONNECTION=sqlite`** and **`DB_DATABASE=:memory:`** under `<php><env>`. You do **not** need MySQL running for `php artisan test`.

### What the suite covers today

- **`tests/Feature/`** — HTTP JSON API tests (`Tests\Feature\Api\V1\…`), Sanctum-authenticated requests, factories, migrations on SQLite in-memory.
- **`tests/Unit/`** — Reserved for fast unit tests (may be empty).

### Expected output (order of magnitude)

After `composer install`, a green run looks like:

```text
Tests:    40 passed (82 assertions)
Duration: ~10–20s
```

Exact **test** and **assertion** counts will change as specs grow; **exit code 0** is the pass criterion.

You may see lines such as **`Tests: 40 deprecated`** in the console: that counts **PHP deprecations emitted during tests** (often from vendor code on PHP 8.5). The bundled **`phpunit.xml`** sets **`failOnDeprecation="false"`** and **`failOnPhpunitWarning="false"`** so those do not fail the job unless you tighten the config.

### Caveats

1. **`composer test`** runs **`php artisan test`**, which expects dev dependency **`nunomaduro/collision`**. If Collision were removed, use **`./vendor/bin/phpunit`** instead.
2. **API validation (422)** for `api/*` JSON requests returns the Bunyan envelope, configured in **`bootstrap/app.php`**:  
   `{ "success": false, "data": null, "message": "…", "errors": { … } }`.
3. **Coverage in `phpunit.xml`:** The committed file is optimized for a **green default test run** without a coverage driver. **`composer run test:coverage`** / **`php artisan test --coverage`** require **PCOV** or **Xdebug** on the PHP binary; there is **no** `--min=` threshold in `composer.json` yet.

### Code coverage (optional)

```bash
cd backend
composer run test:coverage
# equivalent:
# php artisan test --coverage
```

Add HTML/text reports by restoring PHPUnit 11 **`source`** / **`coverage`** blocks in `phpunit.xml` once a driver is installed (see PHPUnit 11 docs).

### Static analysis (PHPStan)

```bash
cd backend
composer run analyze
```

Equivalent: `vendor/bin/phpstan analyse --memory-limit=512M`.

### Code formatting (Laravel Pint)

```bash
cd backend
composer run lint       # dry-run (matches CI)
composer run lint:fix   # apply fixes
```

---

## Running Frontend Tests

### Before `typecheck`: generate Nuxt types

Nuxt writes **`frontend/.nuxt/tsconfig.json`** and generated types during **`nuxi prepare`**. Run after **`npm install`** and in CI **before** `npm run typecheck`:

```bash
cd frontend
npx nuxi prepare
```

### Unit tests (Vitest)

**Command:**
```bash
cd frontend
npm run test
```

**Layout:**

- Specs are discovered from **`tests/**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}`** (see `vitest.config.ts`).
- A minimal **`tests/unit/smoke.spec.ts`** keeps the pipeline green until more suites land.
- **`passWithNoTests: true`** avoids failing when no files match (useful on sparse branches).

**Target areas for future specs:** composables, Pinia stores, components, utilities, form validation.

**Expected output (current smoke):**

```text
✓ tests/unit/smoke.spec.ts (1 test)

Test Files  1 passed (1)
Tests       1 passed (1)
```

### Unit tests (watch mode)

```bash
cd frontend
npm run test:watch
```

### TypeScript (`nuxi typecheck`)

```bash
cd frontend
npx nuxi prepare
npm run typecheck
```

**Configuration:**

- Root **`tsconfig.json`** should **`extends`: `./.nuxt/tsconfig.json`** (Nuxt 3 default).
- **`compilerOptions.ignoreDeprecations`**: `"6.0"` silences TypeScript 6 migration noise for `baseUrl` until Nuxt/tsconfig templates update.

**Tooling-only `// @ts-nocheck`:** `nuxt.config.ts`, `playwright.config.ts`, and `vitest.config.ts` may suppress strict checking where **@nuxt/ui** / **@pinia/nuxt** / **Vite–Vitest** plugin types lag; application Vue/TS files remain fully checked.

### Warnings you may see (often non-fatal)

| Warning | Meaning |
|--------|---------|
| **Tailwind / Nuxt UI** — `Failed to load .nuxt/nuxtui-tailwind.config.mjs` … `defaultExtractor` | **Tailwind v4** exports do not match what **@nuxt/ui** + **@nuxtjs/tailwindcss** expect yet. Build/typecheck may still succeed; fix = upgrade **@nuxt/ui** / Tailwind stack when upstream releases align. |
| **i18n** — `iso` property deprecated | `@nuxtjs/i18n` v9+ will prefer `language` instead of `iso` on locale entries. |
| **npm `EBADENGINE`** | Some ESLint-related packages declare Node **20.19+ / 22.13+ / 24+**; use **Node 20 LTS** in CI for the fewest warnings. |

### ESLint (ESLint 9 flat config)

**Check:**
```bash
cd frontend
npm run lint
```

**Auto-fix:**
```bash
cd frontend
npm run lint:fix
```

**Config file:** `frontend/eslint.config.mjs` (not `.eslintrc`). **Warnings** (e.g. Vue attribute order) may remain; **errors** must be **0** for a clean run.

### Prettier

```bash
cd frontend
npm run format
```

### Pinia

Use the Nuxt module **`@pinia/nuxt`** with **`pinia` ^3** (see `package.json`). In `nuxt.config.ts`, register **`'@pinia/nuxt'`** in `modules`, not the bare string **`'pinia'`**, or Nuxt will error with **Could not load pinia**.

---

## Running E2E Tests

### Playwright E2E tests

**Prerequisites**

- **`@playwright/test`** is already listed in **`frontend/package.json`** (`devDependencies`). Install **browser binaries** once per machine (and in CI):

```bash
cd frontend
npm install
npx playwright install
# Optional on Linux agents — system libs:
# npx playwright install-deps
```

- **`playwright.config.ts`** defines a **`webServer`** that runs **`npm run dev`** when **`CI`** is unset, targeting **`http://localhost:3000`**. Free port **3000** or change **`baseURL` / `webServer`** in config.

**Run all E2E tests:**
```bash
cd frontend
npm run test:e2e
```

**What it will test (as specs are added):**

- Authentication flows (login, register, logout)
- Project / phase / task journeys
- RBAC-sensitive UI routes

**Caveats**

- The **`tests/e2e/`** tree may be **empty or minimal** in early phases; Playwright may report **no tests** until specs exist—this is expected.
- E2E is **slower** and **flakier** than unit tests; run locally with **`npm run test:e2e:ui`** when debugging.

**Example output (when specs exist):**

```text
Running X tests using Y workers
…
X passed
```

### E2E Tests (Headed Mode - Visual Debugging)

```bash
cd frontend
npm run test:e2e -- --headed
```

**Benefit:** Opens browser window showing test execution, helpful for debugging

### E2E Tests with Trace

```bash
cd frontend
npm run test:e2e -- --trace on
```

**Benefit:** Records detailed trace files that can be viewed in Playwright Inspector

### Screenshot Comparison (Visual Regression)

```bash
cd frontend
npm run test:e2e -- --update-snapshots
```

---

## Manual Testing Scenarios

### Scenario 1: Complete Authentication Flow

**Objective:** Verify user can register, login, and logout

**Steps:**

1. Start frontend: `cd frontend && npm run dev`
2. Navigate to http://localhost:3000/auth/login
3. Click "Register" link or navigate to /auth/register
4. Fill registration form:
   - Name: "Test User"
   - Email: "test@example.com"
   - Password: "SecurePass123!"
   - Password Confirm: "SecurePass123!"
   - Role: "Customer"
5. Click "Register" button
6. Verify redirect to login page
7. Enter credentials and click "Login"
8. Verify dashboard loads with welcome message
9. Click user menu → "Logout"
10. Verify redirect to login page

**Expected Behavior:**
- ✅ Registration form validates input
- ✅ Duplicate email prevented
- ✅ Login succeeds with correct credentials
- ✅ Login fails with wrong password
- ✅ Dashboard loads only when authenticated
- ✅ Logout clears session and redirects

**Pass/Fail:** ✅ / ❌

---

### Scenario 2: Create Project (Customer Role)

**Objective:** Verify customer can create a project with phases

**Prerequisites:**
- Customer account created and logged in
- Backend running on http://localhost:8000

**Steps:**

1. From dashboard, click "Create New Project" button
2. Fill project form:
   - Name: "Construction Site A"
   - Description: "Renovation project"
   - Budget: "50000"
3. Click "Create Project"
4. Verify project appears in list
5. Click project to open details
6. Click "Add Phase"
7. Fill phase form:
   - Name: "Foundation"
   - Budget: "10000"
   - Start Date: 2026-04-15
   - End Date: 2026-05-15
8. Click "Create Phase"
9. Verify phase appears in project detail

**Expected Behavior:**
- ✅ Form validates budget (must be number, > 0)
- ✅ Form validates dates (end date > start date)
- ✅ Project created and persisted to database
- ✅ Phase created and linked to project
- ✅ UI reflects updated data

**Pass/Fail:** ✅ / ❌

---

### Scenario 3: RBAC - Customer Cannot Access Admin Routes

**Objective:** Verify role-based access control prevents unauthorized access

**Prerequisites:**
- Customer account logged in
- Browser dev tools open

**Steps:**

1. From dashboard, navigate to http://localhost:3000/admin/users
2. Verify redirection to 403 or dashboard
3. Check browser console for API error: `403 Forbidden`
4. Open Network tab and inspect API call
5. Verify response includes error message about insufficient permissions

**Expected Behavior:**
- ✅ No redirect to admin page occurs
- ✅ API returns 403 status code
- ✅ Error message displayed (or redirect silently)
- ✅ No sensitive data exposed

**Pass/Fail:** ✅ / ❌

---

### Scenario 4: Phase Status Transition

**Objective:** Verify phase status changes follow workflow rules

**Prerequisites:**
- Project with phases created
- Supervising Architect or Admin role
- Backend running

**Steps:**

1. Open project detail view
2. Find "Foundation" phase card
3. Click "Change Status" or dropdown
4. Select "In Progress"
5. Verify status updates to "In Progress"
6. Try to change back to "Pending" (should be blocked)
7. Verify error message if workflow prevents transition

**Expected Behavior:**
- ✅ Valid transitions allowed
- ✅ Invalid transitions blocked with error
- ✅ Status persisted to database
- ✅ UI reflects state change immediately

**Pass/Fail:** ✅ / ❌

---

### Scenario 5: Field Report Submission

**Objective:** Verify field engineer can submit reports with media

**Prerequisites:**
- Field Engineer account logged in
- Project with active phase

**Steps:**

1. Navigate to project details
2. Click "Submit Report" button
3. Fill report form:
   - Text: "Foundation excavation 80% complete"
   - Media: Upload an image (PNG/JPG)
4. Click "Submit"
5. Verify report appears in project timeline
6. Verify media thumbnail displays

**Expected Behavior:**
- ✅ Form validates text required
- ✅ Media upload accepts images/videos
- ✅ Report persisted with timestamp
- ✅ Media accessible from report
- ✅ Notification sent to project stakeholders (if configured)

**Pass/Fail:** ✅ / ❌

---

### Scenario 6: Payment Processing (Customer)

**Objective:** Verify customer can initiate payments

**Prerequisites:**
- Customer account with active projects
- Transactions enabled

**Steps:**

1. Navigate to "Payments" or "Billing" section
2. Verify project balance displays
3. Click "Pay" button
4. Fill payment form:
   - Amount: "5000"
   - Payment Method: (test method)
5. Click "Process Payment"
6. Verify success message
7. Verify balance updated

**Expected Behavior:**
- ✅ Form validates amount (> 0, ≤ balance)
- ✅ Payment processed securely
- ✅ Transaction recorded to database
- ✅ Balance updated for customer
- ✅ Contractor sees pending withdrawal

**Pass/Fail:** ✅ / ❌

---

### Scenario 7: Multi-Language Support (RTL)

**Objective:** Verify Arabic RTL layout and translations

**Prerequisites:**
- Frontend running

**Steps:**

1. Navigate to http://localhost:3000
2. Look for language switcher (top right or menu)
3. Click "عربى" (Arabic) option
4. Verify page layout flips to RTL:
   - Text alignment right-to-left
   - Navigation RTL
   - Form labels RTL
5. Click navigation items and verify Arabic translations
6. Switch back to English (EN)
7. Verify layout flips to LTR

**Expected Behavior:**
- ✅ All text translated to Arabic/English
- ✅ Layout flips correctly (no broken elements)
- ✅ Form direction changes
- ✅ Images/icons remain centered
- ✅ Language preference persists (if stored)

**Pass/Fail:** ✅ / ❌

---

### Scenario 8: API Error Handling

**Objective:** Verify API returns consistent error format

**Prerequisites:**
- Backend running
- API client (Postman, curl, or browser dev tools)

**Steps:**

1. Make invalid API request (e.g., login with bad password):
   ```bash
   curl -X POST http://localhost:8000/api/v1/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email": "user@example.com", "password": "wrong"}'
   ```

2. Verify response format (Bunyan envelope; **message** / **errors** may be **Arabic** strings from the API):
   ```json
   {
     "success": false,
     "data": null,
     "message": "string",
     "errors": {}
   }
   ```

3. Try unauthorized request (without token):
   ```bash
   curl -X GET http://localhost:8000/api/v1/projects
   ```

4. Verify 401 Unauthorized response

5. Try with invalid token:
   ```bash
   curl -X GET http://localhost:8000/api/v1/projects \
     -H "Authorization: Bearer invalid_token"
   ```

6. Verify **401** for missing/invalid bearer token (and **403** when authenticated but forbidden, depending on route)

**Expected Behavior:**
- ✅ All errors follow standard format
- ✅ Appropriate HTTP status codes (400, 401, 403, 404, 500)
- ✅ No stack traces exposed in production
- ✅ Error messages helpful but not revealing internals

**Pass/Fail:** ✅ / ❌

---

### Scenario 9: Database Migrations

**Objective:** Verify database schema correct

**Steps:**

1. Start MySQL in Docker: `docker-compose up -d mysql`
2. Connect to database:
   ```bash
   mysql -h 127.0.0.1 -u root -p bunyan
   ```
3. List tables: `SHOW TABLES;`
4. Verify all expected tables exist (adjust for your migration set):
   - ✅ users
   - ✅ personal_access_tokens (Laravel Sanctum)
   - ✅ roles
   - ✅ permissions
   - ✅ role_permissions
   - ✅ projects
   - ✅ phases
   - ✅ tasks
   - ✅ workflow_configurations
   - ✅ approval_rules
   - ✅ reports
   - ✅ products
   - ✅ orders
   - ✅ order_items
   - ✅ transactions

5. Inspect table structure:
   ```bash
   DESCRIBE users;
   ```

6. Verify columns: e.g. `id`, `name`, `email`, `password`, **`role`** (string), `created_at`, `updated_at` — use `DESCRIBE users;` as truth

**Expected Behavior:**
- ✅ All migrations run successfully
- ✅ All tables created with correct schema
- ✅ Indexes created for performance
- ✅ Foreign keys properly configured
- ✅ Default values set correctly

**Pass/Fail:** ✅ / ❌

---

### Scenario 10: Pre-Commit Hook Validation

**Objective:** Verify linting enforced on commit

**Steps:**

1. Create a test file with formatting issues:
   ```bash
   echo "echo 'test';" > backend/test-bad.php
   ```

2. Stage and attempt to commit:
   ```bash
   git add backend/test-bad.php
   git commit -m "test commit"
   ```

3. Verify pre-commit hook runs automatically
4. Verify commit blocked if linting fails
5. Fix file manually:
   ```bash
   cd backend && vendor/bin/pint app/Models/User.php
   ```

6. Retry commit - should succeed
7. Clean up: `git reset HEAD backend/test-bad.php && rm backend/test-bad.php`

**Expected Behavior:**
- ✅ Pre-commit hook runs automatically
- ✅ Linting violations block commit
- ✅ Fixed files pass validation
- ✅ Clean commits only reach git history

**Pass/Fail:** ✅ / ❌

---

## Verification Checklist

### Backend Environment

- [ ] PHP 8.2+ installed: `php --version`
- [ ] Composer installed: `composer --version`
- [ ] MySQL/SQLite available
- [ ] Redis available (optional)
- [ ] Laravel 11.x installed: `cd backend && php artisan --version`
- [ ] composer.json exists: `cd backend && ls -la composer.json`
- [ ] .env file created: `cd backend && ls -la .env`
- [ ] App key generated: Check `APP_KEY` in .env is not empty
- [ ] Database migrations ready: `cd backend && ls database/migrations/`

### Frontend Environment

- [ ] Node 20+ installed: `node --version`
- [ ] npm installed: `npm --version`
- [ ] Nuxt 3 installed: `cd frontend && npm ls nuxt`
- [ ] Nuxt UI installed: `cd frontend && npm ls @nuxt/ui`
- [ ] Pinia (Nuxt module) installed: `cd frontend && npm ls @pinia/nuxt && npm ls pinia`
- [ ] Tailwind CSS v4 installed: `cd frontend && npm ls tailwindcss`
- [ ] TypeScript installed: `cd frontend && npm ls typescript`
- [ ] Vitest installed: `cd frontend && npm ls vitest`
- [ ] Playwright installed: `cd frontend && npm ls @playwright/test`

### Docker Environment

- [ ] Docker installed: `docker --version`
- [ ] Docker Compose installed: `docker-compose --version`
- [ ] docker-compose.yml exists: `ls -la docker-compose.yml`
- [ ] Docker images available: `docker images` (shows php, node, mysql, redis)

### Git & Pre-Commit

- [ ] Git initialized: `git status`
- [ ] Branch is spec/001-project-initialization: `git branch`
- [ ] Husky installed: `ls -la .husky/pre-commit`
- [ ] lint-staged configured: `cat .lintstagedrc.json`
- [ ] .gitignore excludes vendor/, node_modules/: `cat .gitignore`

### Servers Running (After Starting)

- [ ] Backend: `curl http://localhost:8000` returns 404 (OK, no route)
- [ ] Frontend: `curl http://localhost:3000` returns HTML
- [ ] MySQL: `docker-compose exec mysql mysql --version`
- [ ] Redis: `docker-compose exec redis redis-cli PING` returns PONG

### Test Frameworks Ready

- [ ] PHPUnit configured (**PHPUnit 11** schema): `cd backend && cat phpunit.xml`
- [ ] Vitest configured: `cd frontend && cat vitest.config.ts` (project `pretest` runs `nuxi prepare` so CI jobs that only `npm run test` still get `.nuxt/tsconfig.json`)
- [ ] Playwright configured: `cd frontend && cat playwright.config.ts`
- [ ] Nuxt types generated: `cd frontend && test -f .nuxt/tsconfig.json` (run `npx nuxi prepare` if missing)
- [ ] Test directories exist:
  - [ ] `backend/tests/Unit/` (may be empty)
  - [ ] `backend/tests/Feature/`
  - [ ] `frontend/tests/unit/` (e.g. smoke spec)
  - [ ] `frontend/tests/e2e/` (may be empty until UI specs land)

### CI/CD Workflows

- [ ] Backend CI env template exists and is tracked in git: `test -f backend/ci.env` (GitHub Actions copy it with `cp ci.env .env` from `working-directory: backend` — avoid `.env.ci` filenames that match common global `.gitignore` rules like `.env.*`)
- [ ] `ci.yml` exists (combined / main pipeline): `cat .github/workflows/ci.yml`
- [ ] `backend-ci.yml` exists: `cat .github/workflows/backend-ci.yml`
- [ ] `frontend-ci.yml` exists: `cat .github/workflows/frontend-ci.yml`
- [ ] `pre-commit-guard.yml` exists: `cat .github/workflows/pre-commit-guard.yml`
- [ ] `architecture-governance.yml` exists (repo checks): `cat .github/workflows/architecture-governance.yml`

---

## Troubleshooting

### Backend Issues

#### Issue: `composer install` fails

**Symptom:** `Failed to download laravel/framework from dist`

**Solution:**
```bash
cd backend
composer clearcache
composer install
```

#### Issue: `php artisan key:generate` fails

**Symptom:** `App key already exists`

**Solution:**
- Check `.env` file exists: `ls -la .env`
- Check `APP_KEY` is set: `grep APP_KEY .env`
- If missing, manually add: `echo "APP_KEY=" >> .env && php artisan key:generate`

#### Issue: Migrations fail with "table already exists"

**Symptom:** `SQLSTATE[42S01]: Table 'users' already exists`

**Solution:**
```bash
cd backend
php artisan migrate:reset        # Reset all migrations
php artisan migrate              # Re-run migrations
```

#### Issue: Feature tests fail on foreign keys / missing tables (e.g. `orders`, `personal_access_tokens`)

**Symptom:** SQLite errors during `php artisan test` about unknown tables or FK order.

**Meaning:** Migrations must create **parent** tables before **child** FKs (e.g. **`transactions.order_id`** after **`orders`**). If you add migrations, keep timestamps ordered or ship a **new** forward migration—do not edit **old** migration files per project policy.

**Sanctum:** The **`personal_access_tokens`** table must exist for **`createToken()`** / logout tests; it is created by a first-party migration in **`database/migrations/`**.

#### Issue: PHPStan fails with "memory limit exceeded"

**Solution:** Increase memory limit
```bash
cd backend
vendor/bin/phpstan analyse --memory-limit=1G
```

---

### Frontend Issues

#### Issue: `npm install` fails

**Symptom:** `ERR! code ERESOLVE` or dependency conflicts

**Solution:**

The repo pins compatible peers (**`happy-dom` ^17** with **`@nuxt/test-utils`**, **`@vitest/ui` ^3** with **`vitest` ^3**, **`@pinia/nuxt`** with **`pinia` ^3**). Prefer a clean install from the committed **`package-lock.json`**:

```bash
cd frontend
rm -rf node_modules package-lock.json
npm install
```

If you must override resolution temporarily: `npm install --legacy-peer-deps` (not ideal for CI reproducibility).

#### Issue: `Could not load pinia` / `Is it installed?` during `nuxi typecheck` or `nuxt dev`

**Symptom:** Nuxt fails while resolving the Pinia module.

**Solution:** Ensure **`package.json`** includes **`@pinia/nuxt`** and **`pinia` ^3**, and **`nuxt.config.ts`** lists **`'@pinia/nuxt'`** in **`modules`** (not the bare string **`'pinia'`**). Then:

```bash
cd frontend
npm install
npx nuxi prepare
```

#### Issue: Tailwind / Nuxt UI — `defaultExtractor` or `nuxtui-tailwind.config.mjs`

**Symptom:** Log: `Package subpath './lib/lib/defaultExtractor.js' is not defined by "exports" in ... tailwindcss`.

**Meaning:** **Tailwind CSS v4** package exports do not match what the current **@nuxt/ui** + **@nuxtjs/tailwindcss** stack expects. **`npm run typecheck`** may still exit **0**; **`npm run dev`** / **`nuxt build`** should be verified after upgrades.

**Mitigation:** Track **@nuxt/ui** / Nuxt Tailwind module releases; consider pinning **tailwindcss** to a supported major if builds break.

#### Issue: `npm run dev` fails with port 3000 in use

**Symptom:** `Port 3000 already in use`

**Solution:**
```bash
# Option 1: Kill process on port 3000
lsof -i :3000
kill -9 <PID>

# Option 2: Use different port
cd frontend
npm run dev -- --port 3001
```

#### Issue: TypeScript errors after `npm install` / missing Nuxt globals

**Symptom:** `Cannot find name 'defineNuxtConfig'`, missing `$t`, etc.

**Solution:**

```bash
cd frontend
npx nuxi prepare
npm run typecheck
```

Ensure **`tsconfig.json`** contains **`"extends": "./.nuxt/tsconfig.json"`**. Application code should not rely on **`// @ts-nocheck`**; only **`nuxt.config.ts`**, **`playwright.config.ts`**, and **`vitest.config.ts`** use it intentionally.

#### Issue: Playwright can't find browsers

**Symptom:** `Error: Browsers are not installed. Run npx playwright install`

**Solution:**
```bash
cd frontend
npx playwright install
npx playwright install-deps
```

---

### Docker Issues

#### Issue: `docker-compose up` fails

**Symptom:** `error during connect: This error may indicate that the docker daemon is not running`

**Solution:**
```bash
# Ensure Docker daemon is running
docker ps

# If failed, restart Docker:
# macOS: Open Docker Desktop
# Linux: sudo systemctl restart docker
# Windows: Restart Docker Desktop
```

#### Issue: Port conflicts (3306, 6379, 8000, 3000)

**Symptom:** `bind: address already in use`

**Solution:**
```bash
# Find what's using the port
lsof -i :3306  # MySQL
lsof -i :6379  # Redis
lsof -i :8000  # Backend
lsof -i :3000  # Frontend

# Stop conflicting service
kill -9 <PID>

# Or modify docker-compose.yml ports:
# "3306:3306" → "3307:3306"
```

#### Issue: MySQL won't start in Docker

**Symptom:** `docker-compose: MySQL container exits immediately`

**Solution:**
```bash
docker-compose down -v              # Remove volumes
docker-compose up -d                # Start fresh
docker-compose logs mysql           # Check logs
```

---

### Testing Issues

#### Issue: `php artisan test` reports "No tests found"

**Solution:**
```bash
cd backend
ls tests/Feature/
ls tests/Unit/
cat phpunit.xml
php artisan test --verbose
```

Ensure **`phpunit.xml`** declares the **Feature** / **Unit** test suites (see repository file).

#### Issue: `php artisan test` prints deprecations / exit code confusion

**Symptom:** Console shows **`Constant PDO::MYSQL_ATTR_SSL_CA is deprecated`** or **`Tests: N deprecated`**.

**Meaning:** Often **PHP 8.5 + vendor Laravel** config. Tests may still **pass** (`exit 0`). **`phpunit.xml`** sets relaxed **`failOn*`** flags; **`error_reporting`** omits deprecation bits during the run.

**If you need a silent console:** run tests on **PHP 8.2–8.4** in CI, or accept the noise until upstream Laravel removes the legacy constant from vendor stubs.

#### Issue: `npm run test` reports "No test files found"

**Symptom:** Vitest exits **1** with no matching files.

**Solution:** Add specs under **`frontend/tests/`** or rely on **`passWithNoTests: true`** in **`vitest.config.ts`** (already set in this repo). Smoke file: **`tests/unit/smoke.spec.ts`**.

#### Issue: `npm run test` fails with timeout

**Solution:**
```bash
cd frontend
npm run test -- --reporter=verbose
npm run test tests/unit/smoke.spec.ts
```

#### Issue: E2E tests fail with "Browser not found"

**Solution:**
```bash
cd frontend
npx playwright install          # Install browsers
npx playwright install-deps     # Install system dependencies
npm run test:e2e               # Retry
```

---

## Success Criteria

### Automated gates (match current repo scripts)

✅ **Backend**

- [ ] `cd backend && composer test` (or `php artisan test`) → **exit 0**, all tests green
- [ ] `cd backend && composer run lint` → **exit 0** (Laravel Pint `--test`)
- [ ] `cd backend && composer run analyze` → **exit 0** (PHPStan), when the project enables static analysis in CI

✅ **Frontend**

- [ ] `cd frontend && npx nuxi prepare` → succeeds (generates `.nuxt/`)
- [ ] `cd frontend && npm run typecheck` → **exit 0**
- [ ] `cd frontend && npm run lint` → **exit 0** (warnings may remain; **no errors**)
- [ ] `cd frontend && npm run test` → **exit 0**

✅ **E2E** (when Playwright specs exist and browsers are installed)

- [ ] `cd frontend && npx playwright install` (once per environment)
- [ ] `cd frontend && npm run test:e2e` → **exit 0**

✅ **Docker** (optional)

- [ ] `docker-compose ps` → required services **UP**
- [ ] `docker-compose logs` → no fatal errors

✅ **Manual scenarios**

- [ ] Execute relevant rows from [Manual Testing Scenarios](#manual-testing-scenarios) for your milestone
- [ ] No unexpected **5xx** from API; browser console free of blocking errors

### Coverage targets (aspirational — not enforced in STAGE_01)

- Backend **`composer run test:coverage`** requires **PCOV/Xdebug**; add a **`--min=`** threshold in CI only after reports are stable.
- Frontend **`npm run test:coverage`** is **not** defined in `package.json` yet; add **`vitest run --coverage`** when the team standardizes on a provider.

---

**Testing guide updated:** 2026-04-10 (aligned with repo commands & caveats)  
**Status:** READY FOR PHASE 2  
**Next:** Expand Vitest/Playwright suites and optional coverage gates per phase specs
