# Tasks — Orders

## Backend

- [ ] T001 [P] Add migration `extend_orders_and_order_items_for_stage_19` per `data-model.md`
- [ ] T002 Extend `App\Enums\OrderStatus` with `confirmed` and `completed` cases
- [ ] T003 Update `App\Models\Order` fillable/casts/relations (`quotation`, `supplierProfile`)
- [ ] T004 Update `App\Models\OrderItem` for optional `variant_id` / `description`
- [ ] T005 Extend `App\Repositories\OrderRepository` with role-scoped queries + order number allocation helper
- [ ] T006 Extend `App\Services\InventoryService` with reserve/release helpers for order lines
- [ ] T007 Add `App\Services\OrderService` (create, confirm, cancel, transition, quotation conversion)
- [ ] T008 Refactor `App\Http\Controllers\Api\V1\OrderController` to delegate to `OrderService` + new actions
- [ ] T009 Add `App\Http\Controllers\Api\V1\QuotationOrderController` + `QuotationPolicy::convertToOrder`
- [ ] T010 Add Form Request `UpdateOrderStatusRequest` and wire validation messages (Arabic)
- [ ] T011 Expand `App\Policies\OrderPolicy` for supplier view + confirm/cancel/status rules
- [ ] T012 Register routes in `backend/routes/api.php` with correct middleware groups
- [ ] T013 Update `OrderResource` / `OrderItemResource` for new fields + timeline timestamps
- [ ] T014 Expand `backend/tests/Feature/Api/V1/OrderControllerTest.php` (+ new quotation conversion test)

## Frontend

- [ ] T015 Add `frontend/composables/useOrders.ts`
- [ ] T016 Add `frontend/pages/orders/index.vue` and `frontend/pages/orders/[id].vue` (Nuxt UI, RTL)
- [ ] T017 Add i18n keys for orders in `frontend/i18n/locales/ar.json` and `en.json`

## Spec / QA

- [ ] T018 Mark checklists complete in `specs/runtime/019-orders/checklists/requirements.md` after verification
