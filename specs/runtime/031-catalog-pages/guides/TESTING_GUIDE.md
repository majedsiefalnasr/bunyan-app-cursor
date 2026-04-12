# Testing Guide — Catalog Pages

> **Phase:** 07_FRONTEND_APPLICATION > **Generated:** 2026-04-12T22:22:00Z

## Prerequisites

```bash
cd backend && composer install && cp .env.example .env && php artisan key:generate
php artisan migrate:fresh --seed

cd ../frontend && npm install
```

Set `NUXT_PUBLIC_API_BASE_URL` to your Laravel URL (for example `http://127.0.0.1:8000/api`) in `frontend/.env`.

## Automated checks

```bash
cd backend && php artisan test --filter=CategoryControllerTest
cd backend && php artisan test --filter=ProductControllerTest
cd backend && ./vendor/bin/pint --test

cd frontend && npm run lint && npm run typecheck && npm run test
cd frontend && npm run test:e2e -- --project=chromium tests/e2e/middleware.spec.ts
```

## Manual scenarios

### 1 — Auth gate on products

1. Open a private window (no cookies).
2. Visit `http://127.0.0.1:3000/ar/products`.
3. **Expect:** redirect to `/ar/auth/login` with `redirect` query containing `/ar/products`.

### 2 — Categories grid and detail

1. Log in as a seeded customer (or any role with catalog access).
2. Visit `http://127.0.0.1:3000/ar/categories`.
3. **Expect:** at least one `data-testid="category-card"` when seed data exists.
4. Click a category (for example slug `building-materials` if seeded).
5. **Expect:** category title and a product grid or empty state; network `GET /api/v1/categories/building-materials` returns 200.

### 3 — Product filters and pagination

1. Visit `http://127.0.0.1:3000/ar/products`.
2. Set **min price** / **max price**, toggle **in stock**, pick a **category**, click **Apply**.
3. **Expect:** `GET /api/v1/products` query string includes `min_price`, `max_price`, `in_stock=1`, and `category_id` as selected.
4. If more than one page exists, use pagination; **Expect:** `page` increments in the query and listing updates.

### 4 — Product detail by SKU

1. From listing, open a product that shows a card (SKU-linked URLs when `sku` is returned).
2. **Expect:** URL path uses encoded SKU when applicable; `GET /api/v1/products/{sku}` returns 200.

### 5 — Search page

1. Visit `http://127.0.0.1:3000/ar/search?q=مواد` (or any substring matching seeded product names).
2. **Expect:** listing uses `search` query param; changing `q` in the address bar refreshes results.

### 6 — Supplier profile cards

1. Visit `http://127.0.0.1:3000/ar/suppliers` and open a supplier with products.
2. **Expect:** products render as `CatalogProductCard` tiles with stock and price.

### 7 — Admin category reorder (slug binding)

1. Log in as **admin**.
2. Open `http://127.0.0.1:3000/ar/admin/categories` and trigger a reorder action that calls the API.
3. **Expect:** `PUT /api/v1/categories/{slug}/reorder` (not numeric id) returns 200.
