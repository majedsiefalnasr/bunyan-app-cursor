# Testing Guide — Project Pages

> **Phase:** 07_FRONTEND_APPLICATION > **Generated:** 2026-04-12T23:45:00Z

## Prerequisites

```bash
cd frontend && npm install
cd ../backend && composer install
```

Configure `.env` so MySQL is reachable if you need full Laravel tests or `migrate --pretend`.

## Automated Commands

```bash
# Frontend
cd frontend
npm run lint
npm run typecheck
npm run test
npx playwright test tests/e2e/projects.spec.ts --project=chromium

# Backend (from backend/)
composer run lint
composer run analyze
composer run test
```

## Manual Scenarios

### 1 — Auth gate on project list

1. Log out (clear `auth_token` cookie or use private window).
2. Open `http://127.0.0.1:3000/ar/projects`.
3. Expect redirect to `/ar/auth/login` with `redirect` query containing `/ar/projects`.

### 2 — Creation wizard

1. Log in as **customer** (or admin) with API reachable.
2. Open `/ar/projects/create`.
3. Fill name `مشروع يدوي`, budget `100000`, location `الرياض`.
4. Advance through steps with **Next**, then **Create project** on confirm.
5. Expect navigation to `/ar/projects/{id}` overview with timeline card.

### 3 — Project shell navigation

1. From a project detail URL `/ar/projects/{id}`, use tab buttons: Tasks, Documents, Team, Workflow, Estimates.
2. Confirm URL segments change (`/tasks`, `/documents`, etc.) and content loads without duplicate project headers.

### 4 — Estimates worksheet

1. Open `/ar/projects/{id}/estimates`.
2. Add two lines with quantity and unit price; verify total updates.
3. Refresh the page; data should persist in-session (same browser tab session).

### 5 — Workflow start (authorized role)

1. As customer/contractor/supervising architect/admin, open `/ar/projects/{id}/workflow`.
2. Click **Start workflow**; expect success toast or API error surfaced in UI (depends on backend state).
