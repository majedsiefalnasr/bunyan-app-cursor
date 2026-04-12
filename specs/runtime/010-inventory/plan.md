# Technical Plan — Inventory Management

## Architecture

- **HTTP:** `InventoryController` under `App\Http\Controllers\Api\V1`
- **Auth:** `auth:sanctum` + `role:admin,contractor` on inventory group; `authorize('manageInventory', $product)` on product-scoped routes
- **Domain:** `InventoryService` coordinates `InventoryRepository`, `StockMovementRepository`, optional `ProductRepository` for legacy mirror
- **Persistence:** New tables only; no edits to shipped migrations

## Migrations

1. `create_inventories_table` — FK `product_id` → `products`, nullable FK `variant_id` → `product_variants` (cascade with product), `warehouse_location` string default `default`, unsigned integers for quantities, unique(`product_id`, `variant_id`, `warehouse_location`)
2. `create_stock_movements_table` — FKs product/variant, `type` string(32), signed `quantity` integer, nullable morph-like `reference_type` string + `reference_id` unsignedBigInteger nullable, `notes` text nullable, `created_by` → `users`

## Endpoints implementation

- `index` — query: `page`, `per_page`, `product_id`, `warehouse_location`, `low_stock` (bool)
- `lowStock` — dedicated action reusing repository scope `whereRaw('(quantity - reserved_quantity) <= min_quantity')`
- `adjust` — transactional: lock row via `lockForUpdate()` in transaction, insert movement `type=adjust`, bump inventory, mirror legacy stock fields
- `movements` — paginate `stock_movements` for `product_id`, optional `variant_id` filter

## Scheduling

- `routes/console.php` or `App\Console\Kernel` schedule: daily `inventory:check-low-stock` command calling service to `Log::info` per line (structured context); extensible to notifications later

## Frontend

- `pages/admin/inventory.vue` — `UDashboard` layout pattern like other admin pages; table + modal for delta adjustment calling `PUT /v1/inventory/{id}/adjust` with product id

## Testing

- `tests/Feature/Api/V1/InventoryTest.php` — admin CRUD-like flows, contractor allowed on own product, forbidden on others, unauthenticated 401

## Logging

- Adjust: `Log::info` with `action` => `inventory.adjust`, ids, delta, user_id
- Low-stock command: batch context with count
