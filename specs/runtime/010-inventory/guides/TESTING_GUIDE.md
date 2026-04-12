# Testing Guide — Inventory Management

> **Phase:** 02_CATALOG_AND_INVENTORY > **Generated:** 2026-04-12T18:20:00Z

## Prerequisites

```bash
cd backend && composer install && cp .env.example .env  # if needed
php artisan migrate
cd ../frontend && npm install
```

## Automated

```bash
cd backend && ./vendor/bin/pint --test && php artisan test --no-coverage --filter=InventoryTest
cd ../frontend && npm run typecheck
```

Full backend: `cd backend && php artisan test --no-coverage`

## Manual — Admin UI

1. Log in as **admin** (Sanctum session in Nuxt).
2. Open `https://<host>/admin/inventory` (or local equivalent).
3. Confirm table loads from `GET /api/v1/inventory`.
4. Click **تعديل** on a row, enter **+5** (or **-1**), optional notes, save.
5. Verify `PUT /api/v1/inventory/{productId}/adjust` succeeds and row quantity updates after refresh.

## Manual — API (curl example)

Replace `TOKEN` and `BASE` and `PRODUCT_ID`:

```bash
curl -sS -H "Authorization: Bearer TOKEN" -H "Accept: application/json" \
  "BASE/api/v1/inventory/low-stock" | jq .
```

## Manual — Contractor scope

1. Create contractor user with verified `supplier_profiles` row (`user_id` = contractor).
2. Create product A with `supplier_id` = that profile id; product B with another supplier.
3. As contractor: `PUT /api/v1/inventory/{A}/adjust` with `{"quantity_delta":1}` → **200**.
4. Same for product B → **403**.

## Scheduled job

```bash
cd backend && php artisan inventory:check-low-stock
```

Expect console line `Low stock inventory rows: N` and structured log entry `inventory.low_stock_scan`.
