# Tasks — Admin Pages

> **Phase:** 07_FRONTEND_APPLICATION  
> **Stage:** Admin Pages (`034-admin-pages`)  
> **Generated (UTC):** 2026-04-14T14:28:38Z

- [x] T001 [P] Audit existing admin routes/pages and align to stage scope (`frontend/pages/admin/*`, `frontend/layouts/admin.vue`)
- [x] T002 [P] Refactor `frontend/layouts/admin.vue` to Nuxt UI `UDashboardLayout` + `UDashboardSidebar` and Bunyan design system (RTL + shadow-as-border)
- [x] T003 Update all existing admin pages to consistently set `definePageMeta({ layout: 'admin', middleware: ['auth','role'], roles: [...] })`
- [x] T004 Add `/admin` entry page `frontend/pages/admin/index.vue` (dashboard overview or redirect) with admin-only meta
- [x] T005 Add user detail page `frontend/pages/admin/users/[id].vue` (profile summary + recent activity)
- [x] T006 Implement admin roles page `frontend/pages/admin/roles.vue` wired to `GET /v1/admin/roles` + permission view endpoint
- [x] T007 Create `frontend/components/admin/RolePermissionMatrix.vue` using `UTable` + `UCheckbox` grid and save action (if backend supports mutation; otherwise view-only)
- [x] T008 Update `frontend/pages/admin/users.vue` to use shared table toolbar + consistent empty/loading states (keep `admin/AssignRoleModal.vue`)
- [x] T009 Update `frontend/pages/admin/activity-log.vue` styling to match new admin shell (remove custom slate wrappers if redundant)
- [x] T010 Update `frontend/pages/admin/reports/index.vue` and `frontend/pages/admin/analytics.vue` to match new admin shell styling patterns
- [x] T011 Add platform settings page `frontend/pages/admin/settings.vue` (UI scaffolding + API adapter placeholder if endpoint missing)
- [x] T012 Add notification templates page `frontend/pages/admin/notifications.vue` (UI scaffolding + API adapter placeholder if endpoint missing)
- [x] T013 Add admin navigation entries for: Dashboard, Users, Roles, Categories, Suppliers, Settings, Notifications, Activity Log, Reports, Analytics
- [x] T014 Add composables under `frontend/composables/admin/` (e.g. `useAdminUsers`, `useAdminRoles`, `useAdminActivityLog`, `useAdminSuppliers`, `useAdminReports`) to centralize endpoint URLs + params
- [x] T015 Add/update translations keys for admin nav + page titles (Arabic-first) under `frontend/locales/ar.json` (and `en.json` if required by conventions)
- [x] T016 [P] Vitest: add unit tests for admin composables param building + permission matrix toggle behavior (`frontend/tests/unit/`)
- [x] T017 [P] Playwright: add e2e coverage for admin access vs non-admin redirect, supplier verify, users list render (`frontend/tests/e2e/`)
- [x] T018 Run frontend lint + typecheck + tests; fix any regressions introduced by admin shell refactor
