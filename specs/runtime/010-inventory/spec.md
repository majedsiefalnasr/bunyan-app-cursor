# Feature Specification — Inventory Management (المخزون)

> **Stage:** Inventory Management  
> **Phase:** 02_CATALOG_AND_INVENTORY  
> **Authority:** `specs/phases/02_CATALOG_AND_INVENTORY/STAGE_10_INVENTORY.md`

## Summary

Introduce warehouse-style inventory rows per product (and optional variant), stock movement audit log, REST endpoints for listing, adjustment, movement history, and low-stock reporting. Align with existing catalog (`products`, `product_variants`) and supplier ownership (`supplier_id`). Provide an admin Nuxt screen for stock overview and adjustment; contractors linked to a verified supplier profile may manage inventory only for products they supply.

## User stories

1. **US1 — View stock** — As admin or supplying contractor, I can list inventory levels with filters (product, warehouse, low-stock flag).
2. **US2 — Adjust stock** — As admin or supplying contractor, I can apply signed quantity deltas with optional notes; system records a `stock_movements` row and updates aggregates atomically.
3. **US3 — Audit trail** — As admin or supplying contractor, I can paginate movement history for a product.
4. **US4 — Low stock** — As admin or supplying contractor, I can list SKUs where available quantity (`quantity` − `reserved_quantity`) is at or below `min_quantity`.
5. **US5 — Alerts (async)** — As operations, the platform runs a scheduled low-stock check that logs structured context (notifications UI can subscribe later).

## Functional requirements

### API (v1, Sanctum)

| Method | Path                                    | Roles                 | Description                                                                                                                |
| ------ | --------------------------------------- | --------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| GET    | `/api/v1/inventory`                     | `admin`, `contractor` | Paginated inventory rows; contractor scoped to owned supplier products                                                     |
| PUT    | `/api/v1/inventory/{product}/adjust`    | `admin`, `contractor` | Body: `quantity_delta` (required int), `variant_id` (nullable), `warehouse_location` (optional string), `notes` (nullable) |
| GET    | `/api/v1/inventory/{product}/movements` | `admin`, `contractor` | Paginated `stock_movements` for product                                                                                    |
| GET    | `/api/v1/inventory/low-stock`           | `admin`, `contractor` | Rows failing low threshold; contractor scoped                                                                              |

- All responses use the standard Bunyan JSON contract (`success`, `data`, `message`, `errors` / `error`).
- Route model binding on `{product}` uses `products.id`.

### Data model

- **`inventories`:** `product_id`, nullable `variant_id`, `warehouse_location` (string, default `default`), `quantity` (unsigned int), `reserved_quantity` (unsigned int default 0), `min_quantity` (unsigned int default 0), timestamps. Unique composite `(product_id, variant_id, warehouse_location)` with nullable-safe uniqueness for variant.
- **`stock_movements`:** `product_id`, nullable `variant_id`, `type` (`in`, `out`, `adjust`, `reserve`, `release`), signed `quantity`, nullable `reference_type` / `reference_id` (strings/unsignedBigInteger), `notes` (nullable text), `created_by` (FK `users`), timestamps.

### Layering

- `InventoryController` (thin) → `InventoryService` → `InventoryRepository` / `StockMovementRepository`.
- `AdjustInventoryRequest` validates mutating payloads.
- `ProductPolicy::manageInventory` authorizes admin or contractor whose `supplier_profiles.id` matches `products.supplier_id`.

### Non-goals

- Order checkout reservation integration (reserved fields exist; order linkage deferred to STAGE_19_ORDERS).
- Multi-warehouse transfers as a dedicated workflow (manual adjust per location only in this slice).
- Dedicated `supplier` user role (contractor + supplier profile only).

## Acceptance criteria

- Migrations create tables with indexes and `down()` rollback.
- Feature tests cover admin happy paths, contractor scoped denial, and guest/unauthorized.
- `php artisan migrate --pretend` succeeds.
- Admin inventory page lists API data with RTL layout and Nuxt UI.

## Technical constraints

- Laravel 11, Sanctum, repository + service pattern, Form Requests, API Resources.
- Arabic-first API messages consistent with `BaseController` patterns.
- Forward-only migrations.

## Clarifications

### Session 2026-04-12

1. **Route shape** — Use `{product}` route parameter (not raw `product_id`) for model binding and policy checks.
2. **Supplier UX** — “Supplier” in the phase brief maps to `contractor` users with a `supplier_profiles` row; they may only read/adjust inventory for products where `products.supplier_id` equals their profile id.
3. **Primary stock fields** — New `inventories` table is authoritative for this module; existing `products.quantity_in_stock` / `product_variants.stock_quantity` may be updated opportunistically on adjust for catalog consistency (same delta applied when variant_id null vs set).
