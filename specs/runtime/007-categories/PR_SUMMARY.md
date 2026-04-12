# PR — Categories

## Summary

**Stage:** Categories  
**Phase:** 02_CATALOG_AND_INVENTORY  
**Branch:** `spec/007-categories` → `develop`  
**Tasks:** 12 / 12 completed

## What Changed

### Backend

- Added `categories` migration (self-referencing `parent_id`, soft deletes, slug uniqueness).
- Implemented `Category` model, `CategoryRepository`, `CategoryService`, `CategoryPolicy`, form requests, `CategoryResource`, and `CategoryController`.
- Registered Sanctum routes: authenticated tree read; admin-only create/update/delete/reorder.
- Added `CategorySeeder` and hooked it into `DatabaseSeeder`.
- Added `CategoryControllerTest`, `ApplicationPoliciesTest` category matrix, schema + seeder assertions.

### Frontend

- Added `pages/admin/categories.vue` with tree, create modal, and reorder via API.
- Added `components/ecommerce/CategoryTree.vue`, `CategoryBreadcrumb.vue`, `CategorySelect.vue` and `types/category.ts`.
- Linked “Categories” in `layouts/admin.vue`.

### Database

- `2026_04_12_150000_create_categories_table.php`

## Breaking Changes

- None.

## Testing

- [x] Backend tests (`php artisan test`)
- [x] Frontend tests (`npm run test` in `frontend/`)
- [x] Lint (`composer run lint`, `npm run lint`)
- [x] Type check (`npm run typecheck`, `npm run analyze`)

## Checklist

- [x] RBAC middleware on mutating category routes
- [x] Form Request validation on write endpoints
- [x] Arabic-first labels on admin UI; RTL-friendly spacing (`ps-*`, `marginInlineStart`)
- [x] Error contract via `BaseController`
- [x] Tree built with single flat query + in-memory nesting (no N+1 on index)
- [x] Migration validated with sqlite pretend command (see `VALIDATION_REPORT.md`)

## Related

- Stage File: `specs/phases/02_CATALOG_AND_INVENTORY/STAGE_07_CATEGORIES.md`
- Testing Guide: `specs/runtime/007-categories/guides/TESTING_GUIDE.md`
