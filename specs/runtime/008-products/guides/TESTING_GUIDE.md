# Testing Guide — Products

> **Phase:** 02_CATALOG_AND_INVENTORY > **Generated:** 2026-04-12T15:30:00Z

## Prerequisites

- Backend: `cd backend && composer install && cp .env.example .env` (configure valid MySQL) then `php artisan migrate`
- Frontend: `cd frontend && npm install`

## Automated commands

```bash
# Backend — full suite
cd backend && composer run lint && composer run analyze && composer run test

# Frontend
cd frontend && npm run lint && npm run typecheck && npm run test

# Focused API tests
cd backend && php artisan test --filter=ProductControllerTest
```

## Manual — API (values)

1. **Login** as admin (or any user for read-only). Capture `Bearer` token from Sanctum login response (`POST /api/v1/auth/login` with JSON `email`, `password`).
2. **List products:** `GET https://<host>/api/v1/products?per_page=10` with `Authorization: Bearer <token>`. Expect `success: true` and an array under `data`.
3. **Filter:** create a category via admin categories API (or seeder), note `id`, then `GET /api/v1/products?category_id=<id>` — expect only matching products.
4. **Admin create:** `POST https://<host>/api/v1/admin/products` with body  
   `{"name":"اختبار","category_id":<id>,"price":12.5,"quantity":3}` — expect `201` and `data.category_id` equals `<id>`.
5. **Variant:** `POST .../api/v1/admin/products/<productId>/variants` with `{"name":"وحدة","stock_quantity":5}` — expect `201` and `data.sku` present.
6. **Media row:** `POST .../api/v1/admin/products/<productId>/media` with `{"type":"image","path":"/storage/test/1.png","sort_order":0}` — expect `201`.

## Manual — UI

1. Log in through the Nuxt app (any authenticated role).
2. Open `/products` — list loads; use search box and press Enter or click «بحث» / «Search».
3. Click a product card — `/products/<id>` shows price, stock, and variants section when variants exist.

## Regression

- Confirm existing admin product create with legacy body still works:  
  `{"name":"Cement","category":"building_materials","price":50,"quantity":100}` on `POST /api/v1/admin/products`.
