# STAGE_10 — Inventory Management

> **Phase:** 02_CATALOG_AND_INVENTORY
> **Status:** NOT STARTED
> **Scope:** Stock tracking, availability, low-stock alerts
> **Risk Level:** MEDIUM

## Stage Status

Status: PRODUCTION READY
Risk Level: LOW
Closure Date: 2026-04-12

Scope Closed: Inventory tables, stock movements audit, REST inventory APIs (list, adjust, movements, low-stock), `ProductPolicy::manageInventory`, scheduled low-stock command, admin inventory UI, feature tests — 12 / 12 tasks

Deferred Scope: Order-driven reserve/release automation (STAGE_19_ORDERS); dedicated supplier user role

Architecture Governance Compliance:

- ADR alignment verified (no conflicting ADR introduced)
- RBAC enforcement confirmed (`auth:sanctum`, `role:admin,contractor`, policy on product-scoped routes)
- Service layer architecture maintained
- Error contract compliance verified

Notes: Stage is production ready. Modifications require a new stage.

## Objective

Implement inventory tracking for products with stock management, availability checks, and low-stock alert system.

## Scope

### Backend

- Inventory model (stock levels per product/variant/warehouse)
- Inventory service (adjust stock, reserve, release, transfer)
- Stock movement log (audit trail)
- Low-stock alert job (worker)
- Availability check service
- Inventory API endpoints

### Frontend

- Inventory management page (Admin, Supplier)
- Stock adjustment form
- Stock movement history
- Low-stock alerts dashboard widget

### API Endpoints

| Method | Route                                    | Description           |
| ------ | ---------------------------------------- | --------------------- |
| GET    | /api/v1/inventory                        | List inventory levels |
| PUT    | /api/v1/inventory/{product_id}/adjust    | Adjust stock          |
| GET    | /api/v1/inventory/{product_id}/movements | Stock movement log    |
| GET    | /api/v1/inventory/low-stock              | Low-stock items       |

### Database Schema

| Table           | Columns                                                                                                                                 |
| --------------- | --------------------------------------------------------------------------------------------------------------------------------------- |
| inventories     | id, product_id, variant_id, warehouse_location, quantity, reserved_quantity, min_quantity, updated_at                                   |
| stock_movements | id, product_id, variant_id, type (in/out/adjust/reserve/release), quantity, reference_type, reference_id, notes, created_by, created_at |

## Dependencies

- **Upstream:** STAGE_08_PRODUCTS
- **Downstream:** STAGE_19_ORDERS
