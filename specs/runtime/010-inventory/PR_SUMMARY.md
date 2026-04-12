# PR — Inventory Management

## Summary

**Stage:** Inventory Management  
**Phase:** 02_CATALOG_AND_INVENTORY  
**Branch:** `spec/010-inventory` → `develop`  
**Tasks:** 12 / 12 completed

## What Changed

### Backend

- New tables `inventories`, `stock_movements` with FKs to `products`, `product_variants`, `users`.
- `InventoryService` transactional adjust (row lock on product), movement audit, legacy mirror on `products.quantity_in_stock` / `product_variants.stock_quantity`.
- `InventoryController` + routes under `GET/PUT /api/v1/inventory…` with `auth:sanctum` and `role:admin,contractor`.
- `ProductPolicy::manageInventory` for admin vs supplier-linked contractor.
- Artisan `inventory:check-low-stock` scheduled daily.
- Feature tests: `tests/Feature/Api/V1/InventoryTest.php`.

### Frontend

- `pages/admin/inventory.vue` (admin layout, table + adjust modal) and sidebar link in `layouts/admin.vue`.

## Breaking Changes

- None intended. New routes and tables only.

## Testing

- [x] `cd backend && ./vendor/bin/pint --test`
- [x] `cd backend && php artisan test --no-coverage`
- [x] `cd frontend && npm run typecheck`
- [ ] `php artisan migrate --pretend` against real MySQL (optional; see `reports/LOCAL_CI_REPORT.md` if skipped locally)

## Related

- Stage file: `specs/phases/02_CATALOG_AND_INVENTORY/STAGE_10_INVENTORY.md`
- Testing guide: `specs/runtime/010-inventory/guides/TESTING_GUIDE.md`
