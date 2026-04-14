# Plan — Admin Pages

> **Phase:** 07_FRONTEND_APPLICATION  
> **Stage:** Admin Pages (`034-admin-pages`)  
> **Generated (UTC):** 2026-04-14T14:24:38Z

## Current State Snapshot (from repo)

- Admin UX exists under `frontend/pages/admin/*` and uses `layouts/admin.vue` + route middleware `auth` + `role`.
- Backend provides admin APIs under `/api/v1/admin/*` for users/roles/activity-log/analytics reports and admin-guarded non-prefixed routes for some actions (supplier verify, category CRUD/reorder).

## Target State (this stage)

- Unified Admin shell using **Nuxt UI dashboard primitives** (`UDashboardLayout`, `UDashboardSidebar`) and Bunyan design system (`DESIGN.md`).
- Admin routes protected via Nuxt middleware (`auth` + `role`) for **UX gating**, with consistent handling of 401/403 from API.
- Page coverage aligned to stage scope:
  - `/admin` (dashboard overview)
  - `/admin/users` and `/admin/users/:id`
  - `/admin/roles`
  - `/admin/categories`
  - `/admin/suppliers`
  - `/admin/settings`
  - `/admin/notifications`
  - `/admin/activity-log`
  - `/admin/reports`
  - `/admin/analytics`

## Architecture (Frontend)

- **Layouts**: `frontend/layouts/admin.vue` becomes the single Admin shell, implemented with `UDashboardLayout` + `UDashboardSidebar`.
- **Pages**: Pages remain thin; fetch data via composables (`frontend/composables/admin/*`) which call `useApi().apiFetch`.
- **Components**:
  - Keep atomic, reusable admin UI pieces under `frontend/components/admin/` (table toolbars, modals).
  - Prefer Nuxt UI components over custom markup.
- **RBAC UX**:
  - Continue using `frontend/middleware/role.ts` with `definePageMeta({ roles: [...] })`.
  - Ensure every `/admin/**` page sets `middleware: ['auth', 'role']` and `roles: ['admin']` (or explicit multi-role when justified).

## Data & API Contracts (no backend changes planned)

Use existing endpoints first; map them behind composables so pages don’t encode URL shapes:

- Admin users:
  - `GET /v1/admin/users?page&per_page&role?`
  - `POST /v1/admin/users/{user}/role`
  - `DELETE /v1/admin/users/{user}/role`
- Admin roles:
  - `GET /v1/admin/roles`
  - `GET /v1/admin/roles/{role}/permissions`
- Categories (admin-guarded write):
  - `GET /v1/categories`, `GET /v1/categories/{category}`
  - `POST /v1/categories`
  - `PUT /v1/categories/{category}`
  - `DELETE /v1/categories/{category}`
  - `PUT /v1/categories/{category}/reorder`
- Suppliers:
  - `GET /v1/admin/suppliers`
  - `PUT /v1/suppliers/{supplierProfile}/verify`
- Activity log:
  - `GET /v1/admin/activity-log?page&per_page`
- Analytics:
  - `GET /v1/analytics/overview`, `GET /v1/analytics/trends` (already used by `/admin/analytics`)
  - Reports center:
    - `GET /v1/admin/analytics/reports/types`
    - `GET /v1/admin/analytics/reports/{type}?date_from&date_to`
    - `GET /v1/admin/analytics/reports/{type}/export?format&date_from&date_to`

## Implementation Outline

### 1) Admin shell refactor

- Refactor `frontend/layouts/admin.vue` from custom HTML to `UDashboardLayout` + `UDashboardSidebar`.
- Replace hardcoded English labels with i18n keys (Arabic-first) and ensure RTL-friendly nav ordering.
- Provide a shared “content container” area that all admin pages inherit without duplicating wrappers.

### 2) Standardize admin pages

- Ensure each admin page:
  - declares `layout: 'admin'` consistently (some pages currently omit it)
  - uses consistent page header patterns (title + optional actions)
  - uses consistent loading/empty/error states

### 3) Fill missing pages

- Add `/admin` (or redirect `/admin` → existing dashboard route) aligned with stage scope.
- Add `/admin/users/[id].vue` (user detail + activity snippet).
- Add `/admin/roles.vue` (role list + permission matrix UI).
- Add `/admin/settings.vue` (platform settings form UI; API wiring depends on existing endpoints discovery).
- Add `/admin/notifications.vue` (template editor UI; API wiring depends on existing endpoints discovery).

### 4) Testing

- **Vitest**: composables for admin users (query params), permission matrix state updates.
- **Playwright**: admin access works; non-admin redirected; suppliers verify action; roles matrix toggles persist (if backend supports).

## Risks & Mitigations

- **MEDIUM**: Some scoped pages (settings, notification templates) may not have existing backend endpoints.
  - Mitigation: implement UI scaffolding with clear “API not available” empty states; if backend work is required, spin a separate backend stage rather than slipping scope silently.
