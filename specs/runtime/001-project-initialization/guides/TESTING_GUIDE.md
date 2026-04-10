# STAGE_01 Testing Guide — Local Development & Validation

**Status:** COMPLETE  
**Date:** 2026-04-10  
**Scope:** Backend (Laravel), Frontend (Nuxt.js), Docker, CI/CD, E2E

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
- **PHP:** 8.2 or higher
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

### Unit Tests

**Command:**
```bash
cd backend
php artisan test
```

**What It Tests:**
- Service layer logic (ProjectService, PhaseService, etc.)
- Repository queries and relationships
- Eloquent model scopes and accessors
- Validation logic
- Utility functions

**Expected Output:**
```
Tests:  25 passed (XX assertions)
Duration: X.XXs
```

### Feature Tests (Integration Tests)

**Command:**
```bash
cd backend
php artisan test --filter Feature
```

**What It Tests:**
- API endpoints with real database
- HTTP request/response cycles
- Authentication flows
- RBAC authorization
- Error handling and status codes

**Expected Output:**
```
Tests:  35 passed (XX assertions)
Duration: X.XXs
```

### Code Coverage

**Command:**
```bash
cd backend
php artisan test --coverage --min=80
```

**Requirements:**
- Minimum 80% code coverage across all files
- Coverage report generated in `coverage/` directory
- Includes line coverage, branch coverage, method coverage

### Static Analysis

**Command:**
```bash
cd backend
vendor/bin/phpstan analyse --memory-limit=512M
```

**What It Checks:**
- Type errors
- Undefined variables
- Unused imports
- Logical errors
- Memory limits

**Expected Output:**
```
[OK] No errors
```

### Code Formatting

**Check (Dry Run):**
```bash
cd backend
php-cs-fixer fix --dry-run --diff
```

**Auto-Fix:**
```bash
cd backend
php-cs-fixer fix
```

---

## Running Frontend Tests

### Unit Tests

**Command:**
```bash
cd frontend
npm run test
```

**What It Tests:**
- Composable logic (useAuth, useProject, useApi, etc.)
- Pinia store actions and getters
- Vue component logic
- Utility functions
- Form validation

**Expected Output:**
```
✓ composables/useAuth.spec.ts (12 tests)
✓ stores/auth.spec.ts (8 tests)
✓ composables/useApi.spec.ts (10 tests)

Test Files: 3 passed (3)
Tests: 30 passed (30)
```

### Unit Tests (Watch Mode)

**Command:**
```bash
cd frontend
npm run test:watch
```

**Benefit:** Re-runs tests on file changes, useful during development

### Code Coverage

**Command:**
```bash
cd frontend
npm run test:coverage
```

**Requirements:**
- Minimum 70% code coverage
- Coverage report in `coverage/` directory

### TypeScript Type Checking

**Command:**
```bash
cd frontend
npm run typecheck
```

**What It Checks:**
- Type errors in .ts/.tsx/.vue files
- Unused imports
- Type mismatches
- Missing type annotations

**Expected Output:**
```
✓ No type errors detected
```

### ESLint Code Quality

**Check:**
```bash
cd frontend
npm run lint
```

**Auto-Fix:**
```bash
cd frontend
npm run lint:fix
```

**What It Checks:**
- Code style compliance
- Unused variables
- Naming conventions
- Import organization
- Vue best practices

### Prettier Code Formatting

**Command:**
```bash
cd frontend
npx prettier --write . --check
```

---

## Running E2E Tests

### Playwright E2E Tests

**Prerequisites:**
```bash
cd frontend
npm install -D @playwright/test @nuxt/test-utils
```

**Run All E2E Tests:**
```bash
cd frontend
npm run test:e2e
```

**What It Tests:**
- User authentication flows (login, register, logout)
- Project creation and management
- Phase status transitions and approvals
- Task assignment and completion
- RBAC enforcement in UI

**Expected Output:**
```
✓ tests/e2e/auth.spec.ts (2 tests)
✓ tests/e2e/project-creation.spec.ts (2 tests)
✓ tests/e2e/phase-transition.spec.ts (2 tests)
✓ tests/e2e/task-completion.spec.ts (2 tests)

Tests: 8 passed
Duration: X.XXs
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

2. Verify response format:
   ```json
   {
     "success": false,
     "data": null,
     "message": "Unauthorized",
     "errors": {
       "password": ["Invalid credentials"]
     }
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

6. Verify 403 Forbidden response

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
4. Verify all expected tables exist:
   - ✅ users
   - ✅ roles
   - ✅ permissions
   - ✅ projects
   - ✅ phases
   - ✅ tasks
   - ✅ reports
   - ✅ transactions
   - ✅ products
   - ✅ orders
   - ✅ workflow_configurations
   - ✅ approval_rules

5. Inspect table structure:
   ```bash
   DESCRIBE users;
   ```

6. Verify columns: id, name, email, password, role_id, created_at, updated_at

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
   php-cs-fixer fix backend/test-bad.php
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

- [ ] PHPUnit configured: `cd backend && cat phpunit.xml`
- [ ] Vitest configured: `cd frontend && cat vitest.config.ts`
- [ ] Playwright configured: `cd frontend && cat playwright.config.ts`
- [ ] Test directories exist:
  - [ ] `backend/tests/Unit/`
  - [ ] `backend/tests/Feature/`
  - [ ] `frontend/tests/unit/`
  - [ ] `frontend/tests/e2e/`

### CI/CD Workflows

- [ ] backend-ci.yml exists: `cat .github/workflows/backend-ci.yml`
- [ ] frontend-ci.yml exists: `cat .github/workflows/frontend-ci.yml`
- [ ] pre-commit-guard.yml exists: `cat .github/workflows/pre-commit-guard.yml`

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
```bash
cd frontend
rm -rf node_modules package-lock.json
npm install --legacy-peer-deps
```

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

#### Issue: TypeScript errors after npm install

**Symptom:** `TS2688: Cannot find type definition for 'node'`

**Solution:**
```bash
cd frontend
npm install -D @types/node
npm run typecheck
```

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

#### Issue: `php artisan test` fails with "No tests found"

**Solution:**
```bash
cd backend
# Verify tests directory exists
ls tests/Feature/
ls tests/Unit/

# Check phpunit.xml points to tests directory
cat phpunit.xml

# Run with verbose
php artisan test --verbose
```

#### Issue: `npm run test` fails with timeout

**Solution:**
```bash
cd frontend
# Increase timeout
npm run test -- --reporter=verbose

# Or run single test file
npm run test tests/composables/useAuth.spec.ts
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

### All Tests Passing

✅ **Backend:**
- [ ] `php artisan test` → All tests pass, ≥80% coverage
- [ ] `vendor/bin/phpstan analyse` → [OK] No errors
- [ ] `php-cs-fixer fix --dry-run` → No violations

✅ **Frontend:**
- [ ] `npm run test` → All tests pass, ≥70% coverage
- [ ] `npm run typecheck` → No type errors
- [ ] `npm run lint` → No linting errors

✅ **E2E:**
- [ ] `npm run test:e2e` → All tests pass

✅ **Docker:**
- [ ] `docker-compose ps` → All services UP
- [ ] `docker-compose logs` → No errors, health checks pass

✅ **Manual Scenarios:**
- [ ] All 10 scenarios above pass
- [ ] No console errors in browser
- [ ] No server 5xx errors

---

**Testing Guide Generated:** 2026-04-10  
**Status:** READY FOR PHASE 2  
**Next:** Implement feature tests for Phase 2 database and API layer
