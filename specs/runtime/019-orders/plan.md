# Technical Plan — Orders (STAGE_19)

## Architecture

- **Stack:** Laravel API + Nuxt 3 + Sanctum; RBAC via `role:` middleware in `backend/routes/api.php`.
- **Layers:** Controllers → Services → Repositories → Models (strict: no Eloquent in services).

### Backend Touchpoints

- **Service (new):** `App\Services\OrderService` — numbering, create from payload, convert quotation, confirm/cancel, transition validation, totals orchestration.
- **Service (extend):** `App\Services\InventoryService` — `reserveForOrderLine`, `releaseReservationsForOrder` using `InventoryRepository::lockProductForUpdate` / `findLineForUpdate`.
- **Repository (extend):** `App\Repositories\OrderRepository` — scoped lists, `nextOrderSequenceForDate`, find with relations.
- **Controllers:** Thin `OrderController` delegating to `OrderService`; new `QuotationOrderController` (or nested invokable) for `POST quotations/{quotation}/to-order`.
- **Policies:** Extend `OrderPolicy` with `confirm`, `cancel`, `transitionStatus`, `viewForSupplier`, `convertQuotation`.
- **Form Requests:** `CreateOrderRequest` (extend), `UpdateOrderStatusRequest`, `ConvertQuotationToOrderRequest` (implicit via policy + typehints).

### Frontend

- `frontend/composables/useOrders.ts` — list, show, confirm, cancel.
- `frontend/pages/orders/index.vue`, `frontend/pages/orders/[id].vue` — Nuxt UI, RTL, `middleware: ['auth','role']` as needed.
- `frontend/i18n/locales/*.json` keys `order.*`.

## Database

Forward-only migration `extend_orders_for_stage_19` (name timestamped at implementation):

- `orders`: `order_number` (string, unique, nullable until backfill), `supplier_id` nullable FK → `supplier_profiles`, `quotation_id` nullable FK → `quotations`, `subtotal`, `tax_amount`, `shipping_amount` decimals default 0, `confirmed_at`, `shipped_at`, `delivered_at` nullable timestamps.
- `order_items`: nullable `variant_id` FK → `product_variants` (restrict), optional `description` if needed for quotation lines.

Backfill: set `order_number` for existing rows in same migration transaction using generated pattern.

## API Surface

See `contracts/api.md`.

## Status Transitions

Centralized in `OrderService::assertTransition($from, $to)` matching spec matrix.

## Logging

Structured `Log::info` on confirm/cancel/reserve/release with `order_id`, `user_id`, `action`.

## Testing

Feature tests: RBAC matrix for list/show; confirm reserves; cancel releases; quotation conversion guards; illegal transition 422.

## Risks

- Concurrency on inventory: mitigate with `lockForUpdate` on inventory rows in same transaction as order status write.
