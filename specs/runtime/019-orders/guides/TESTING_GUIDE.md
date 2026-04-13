# Testing Guide — Orders

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-13T19:15:00Z

## Prerequisites

```bash
cd backend && composer install && cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed
cd ../frontend && npm install
```

Use a **customer** Sanctum token (e.g. from login API) and base URL `http://localhost:8000/api`.

## Running Tests

### Backend — Orders

```bash
cd backend
php artisan test --filter=OrderControllerTest
php artisan test --filter=test_order_policy_matrix
php artisan test tests/Unit/Enums/OtherEnumsTest.php --filter=order_status
```

### Frontend

```bash
cd frontend
npm run typecheck
npm run lint
```

## Manual Test Scenarios

### Scenario 1 — Create and list orders

**Preconditions:** Customer user `id=2` (example), at least one product `id=5`.

1. `POST /api/v1/auth/login` with customer credentials → capture `token`.
2. `POST /api/v1/orders` header `Authorization: Bearer {token}` body:
   ```json
   {
     "items": [{ "product_id": 5, "quantity": 2, "price": "120.50" }]
   }
   ```
3. Expect `201`, `data.order_number` like `BNY-20260413-0001`, `data.status` = `pending`.
4. `GET /api/v1/orders` → response includes the new order in `data`.

### Scenario 2 — Confirm reserves inventory

**Preconditions:** Inventory row for product `5` in `warehouse_location=default` with `quantity=100`, `reserved_quantity=0`.

1. Create order as in Scenario 1 for `product_id=5`, `quantity=3`.
2. `PUT /api/v1/orders/{id}/confirm` as the same customer.
3. Expect `200`, `data.status` = `confirmed`.
4. Query `inventories` for `product_id=5` → `reserved_quantity` increased by `3`.

### Scenario 3 — Cancel releases reservation

1. On the confirmed order from Scenario 2, `PUT /api/v1/orders/{id}/cancel`.
2. Expect `200`, `data.status` = `cancelled`.
3. `reserved_quantity` returns to previous value.

### Scenario 4 — Quotation → order

**Preconditions:** RFQ owned by customer with quotation `status=accepted` (per seed or RFQ flow).

1. `POST /api/v1/quotations/{quotationId}/to-order` as customer owner.
2. Expect `201`, `data.quotation_id` populated, `data.supplier_id` matches quotation.

### Scenario 5 — UI

1. Log into Nuxt app as customer.
2. Open `/orders` — list shows cards with order numbers.
3. Open `/orders/{id}` for a pending order — **Confirm** and **Cancel** buttons visible; confirm transitions to confirmed in UI after refresh.
