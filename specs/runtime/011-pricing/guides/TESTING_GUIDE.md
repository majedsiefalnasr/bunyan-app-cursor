# Testing Guide — Pricing

> **Phase:** 02_CATALOG_AND_INVENTORY > **Generated:** 2026-04-12T18:45:00Z

## Prerequisites

```bash
cd backend && composer install && cp .env.example .env && php artisan key:generate
# Configure MySQL in .env, then:
php artisan migrate

cd ../frontend && npm install
```

## Automated tests

```bash
cd backend
php artisan test --filter=PricingTest
```

```bash
cd frontend
npm run typecheck
npm run lint
```

## Manual scenario 1 — Admin tier CRUD

**Preconditions:** Admin user (role `admin`), existing product with numeric `id` (example: `1`).

1. Log in to the Nuxt app as admin.
2. Open `https://<app-host>/admin/products/1/pricing` (replace host and id).
3. Confirm the tier table loads (or shows one empty row).
4. Set row 1: min `1`, max `10`, unit price `100.00`, variant empty.
5. Click **حفظ** (Save). Expect success toast.
6. Call API (Bearer admin token):

```bash
curl -s -H "Authorization: Bearer <TOKEN>" \
  https://<api-host>/api/v1/products/1/pricing | jq
```

Expect `data.tiers` length ≥ 1 with `unit_price` `"100.00"`.

## Manual scenario 2 — Calculate price

**Preconditions:** Authenticated user, product `id=1` with tier `1–10` at `40.00` SAR/unit.

```bash
curl -s -X POST -H "Authorization: Bearer <TOKEN>" -H "Content-Type: application/json" \
  -d '{"product_id":1,"quantity":3}' \
  https://<api-host>/api/v1/pricing/calculate | jq
```

Expect `data.unit_price` = `"40.00"`, `data.line_total` = `"120.00"`, `data.currency` = `"SAR"`.

## Manual scenario 3 — Price history

1. As admin, `PUT /api/v1/admin/products/1` with body `{ "price": 99.99 }` (adjust id).
2. Query DB: `SELECT * FROM price_histories WHERE product_id = 1 ORDER BY id DESC LIMIT 1;`
3. Expect `old_price` / `new_price` reflecting the change and `changed_by` = admin user id.

## Manual scenario 4 — Product detail tiers

1. As any authenticated user, open `/products/1`.
2. Confirm **أسعار الكمية** section appears when tiers exist, with SAR-formatted amounts.

## Manual scenario 5 — RBAC negative

1. As customer, `PUT /api/v1/admin/products/1/pricing` with any JSON body.
2. Expect HTTP **403**.
