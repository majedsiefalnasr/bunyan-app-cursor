# Quickstart — Pricing

## Backend

```bash
cd backend
composer install
php artisan migrate
php artisan test --filter=Pricing
```

## Try the API (after seed + auth token)

1. `GET /api/v1/products/1/pricing` — Bearer token; returns `{ success, data: { tiers: [...] } }`.
2. `PUT /api/v1/admin/products/1/pricing` — admin token; body `{ "tiers": [{ "min_quantity": 1, "max_quantity": 9, "unit_price": "100.00", "product_variant_id": null }] }`.
3. `POST /api/v1/pricing/calculate` — body `{ "product_id": 1, "quantity": 5 }`.

## Frontend

```bash
cd frontend
npm install
npm run dev
```

- Open `/admin/products/1/pricing` as admin.
- Open `/products/1` to see tier table when configured.
