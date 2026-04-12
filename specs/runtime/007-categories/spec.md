# Specification — Categories (STAGE_07)

**Phase:** 02_CATALOG_AND_INVENTORY  
**Authority:** `specs/phases/02_CATALOG_AND_INVENTORY/STAGE_07_CATEGORIES.md`

## Summary

Deliver a hierarchical category system for construction products and services: persisted tree in MySQL, REST API under `/api/v1`, admin management UI in Nuxt, and reusable client components for catalog flows.

## User stories

1. **US1 — Browse category tree**  
   As an authenticated user, I can retrieve the category tree so that catalog screens can show grouped materials.

2. **US2 — Admin CRUD**  
   As an admin, I can create, update, soft-delete, and reorder categories while preserving tree integrity.

3. **US3 — Admin UI**  
   As an admin, I can manage categories in the dashboard with a tree view, breadcrumbs, and a selector suitable for linking products later.

## Functional requirements

### Backend

- `categories` table: `id`, `parent_id` (nullable self-FK), `name_ar`, `name_en`, `slug` (unique), `icon` (nullable), `sort_order` (int), `is_active` (bool), timestamps, `deleted_at`.
- Eloquent `Category` with parent/children relations and soft deletes.
- `CategoryRepository` for tree assembly and sibling queries (no business rules in repository beyond query composition).
- `CategoryService` for create/update/delete/reorder/move with validation (no descendant cycles, no delete when children exist).
- Form requests for store, update, reorder.
- `CategoryResource` for API output with nested `children` when loaded.
- `CategoryPolicy`: any authenticated user may view active categories; admin for mutations.
- Routes (Sanctum):
  - `GET /api/v1/categories` — tree for authenticated users.
  - `GET /api/v1/categories/{id}` — detail.
  - `POST|PUT|DELETE /api/v1/categories` / `{id}` — admin only (`role:admin`).
  - `PUT /api/v1/categories/{id}/reorder` — admin only; body `sort_order` among siblings.
- Seeder with default Arabic/English construction categories (مواد بناء، كهرباء، سباكة، تشطيبات).

### Frontend

- Admin page `/admin/categories` (auth + admin role middleware) listing tree with drag-and-drop reorder among siblings.
- `CategoryTree`, `CategoryBreadcrumb`, `CategorySelect` components (Arabic RTL, Nuxt UI).

### Non-goals (this stage)

- Replacing the legacy `products.category` string column with `category_id` (reserved for STAGE_08 Products).

## Acceptance criteria

- All category endpoints enforce Sanctum + RBAC as specified.
- Tree responses match resource contract; inactive categories hidden from non-admin list unless explicitly requested by admin UI (admin list shows all).
- Reorder updates `sort_order` consistently for siblings.
- PHPUnit feature tests cover RBAC matrix for category routes.

## Clarifications

### Session 2026-04-12

- **List visibility:** Public tree listing requires authentication (consistent with existing product list). Guests receive 401 on category index.
- **Inactive categories:** Non-admin `GET /categories` returns only `is_active=true`. Admin uses same endpoint with query `include_inactive=1` to manage all nodes.
- **Delete:** Soft delete only; blocked when child categories exist.
