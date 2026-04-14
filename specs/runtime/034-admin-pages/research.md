# Research — Admin Pages

> **Generated (UTC):** 2026-04-14T14:24:38Z

## Repo Discovery Notes

### Existing frontend artifacts

- **Admin layout**: `frontend/layouts/admin.vue` exists but is custom (dark slate) and not using `UDashboardLayout`.
- **RBAC middleware (UX)**: `frontend/middleware/role.ts` reads `to.meta.roles` and redirects unauthorized users.
- **Admin pages present**:
  - `frontend/pages/admin/users.vue`
  - `frontend/pages/admin/categories.vue`
  - `frontend/pages/admin/suppliers.vue`
  - `frontend/pages/admin/activity-log.vue`
  - `frontend/pages/admin/reports/index.vue`
  - `frontend/pages/admin/analytics.vue`
  - plus other admin pages not in this stage scope (e.g. workflows/inventory)
- **Existing admin component**: `frontend/components/admin/AssignRoleModal.vue`

### Existing backend endpoints relevant to admin scope

Confirmed in `backend/routes/api.php`:

- Admin prefix: `/api/v1/admin/*` protected by `auth:sanctum` + `role:admin`
  - roles, role permissions, users list, assign/remove role
  - admin suppliers list
  - admin activity log
  - business analytics report types/show/export
- Non-prefixed but admin-guarded:
  - supplier verify: `PUT /api/v1/suppliers/{supplierProfile}/verify`
  - category CRUD/reorder: `POST/PUT/DELETE/PUT reorder` under `/api/v1/categories/*`

## Open Gaps to Confirm During Implementation

- Whether backend exposes endpoints for:
  - Admin dashboard KPIs (if `/admin` needs backend data beyond analytics overview)
  - Notification templates CRUD
  - Platform settings CRUD
- User detail endpoint contract for `/admin/users/:id` (may reuse existing non-admin `UserController` endpoints or require a dedicated admin endpoint)
