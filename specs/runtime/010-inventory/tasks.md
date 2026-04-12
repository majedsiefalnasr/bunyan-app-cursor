# Tasks — Inventory Management

- [ ] T001 [P] [US1] Add migration `backend/database/migrations/2026_04_12_180000_create_inventories_table.php`
- [ ] T002 [P] [US1] Add migration `backend/database/migrations/2026_04_12_180001_create_stock_movements_table.php`
- [ ] T003 [US1] Add Eloquent models `backend/app/Models/Inventory.php` and `StockMovement.php` with relations
- [ ] T004 [US1] Add repositories `backend/app/Repositories/InventoryRepository.php`, `StockMovementRepository.php`
- [ ] T005 [US2] Add `backend/app/Services/InventoryService.php` (adjust, list scopes, low-stock query, legacy mirror)
- [ ] T006 [US2] Extend `backend/app/Policies/ProductPolicy.php` with `manageInventory` authorization
- [ ] T007 [US2] Add `AdjustInventoryRequest`, `InventoryResource`, `StockMovementResource` under `backend/app/Http/`
- [ ] T008 [US1] Add `InventoryController` and register routes in `backend/routes/api.php`
- [ ] T009 [US1] Add feature tests `backend/tests/Feature/Api/V1/InventoryTest.php`
- [ ] T010 [US5] Add Artisan command + schedule entry for low-stock scan (`backend/routes/console.php`)
- [ ] T011 [US5] Add `frontend/pages/admin/inventory.vue` for admin stock table and adjust modal
- [ ] T012 [US1] Mark checklists complete and run validation (`composer test`, `migrate --pretend`)
