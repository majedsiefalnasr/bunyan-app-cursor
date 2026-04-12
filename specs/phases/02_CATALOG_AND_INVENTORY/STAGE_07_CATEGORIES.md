# STAGE_07 — Categories

> **Phase:** 02_CATALOG_AND_INVENTORY
> **Status:** PRODUCTION READY
> **Scope:** Product/service category hierarchy, nested categories
> **Risk Level:** LOW

## Stage Status

Status: PRODUCTION READY  
Step: stage_production_ready  
Risk Level: LOW  
Closure Date: 2026-04-12

Scope Closed: Hierarchical categories API, admin UI, seeder, tests, validation pipeline green; 12/12 tasks.

Deferred Scope: `products.category_id` FK and migration from string `category` (STAGE_08).

Architecture Governance Compliance:

- ADR alignment: additive catalog module within existing API/RBAC patterns
- RBAC: Sanctum + `role:admin` on mutations; `include_inactive` admin-only
- Service/repository layering; thin controller; error contract preserved

Notes: Stage production ready. Changes require a new stage or formal amendment.

## Objective

Implement hierarchical category system for organizing construction products and services.

## Scope

### Backend

- Category Eloquent model with parent-child self-relation
- Category repository with tree traversal
- Category service (CRUD, reorder, move)
- Category API resource
- Category Form Request validation
- Seeder with default construction categories (e.g., مواد بناء، كهرباء، سباكة، تشطيبات)

### Frontend

- Category management page (Admin)
- Category tree component with drag-and-drop reorder
- Category breadcrumb component
- Category selector dropdown (used by products)

### API Endpoints

| Method | Route                           | Description            |
| ------ | ------------------------------- | ---------------------- |
| GET    | /api/v1/categories              | List categories (tree) |
| POST   | /api/v1/categories              | Create category        |
| GET    | /api/v1/categories/{id}         | Get category details   |
| PUT    | /api/v1/categories/{id}         | Update category        |
| DELETE | /api/v1/categories/{id}         | Delete category        |
| PUT    | /api/v1/categories/{id}/reorder | Reorder category       |

### Database Schema

| Table      | Columns                                                                                                |
| ---------- | ------------------------------------------------------------------------------------------------------ |
| categories | id, parent_id, name_ar, name_en, slug, icon, sort_order, is_active, created_at, updated_at, deleted_at |

## Dependencies

- **Upstream:** STAGE_06_API_FOUNDATION
- **Downstream:** STAGE_08_PRODUCTS
