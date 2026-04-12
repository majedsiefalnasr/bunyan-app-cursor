# Quickstart — Inventory

1. `cd backend && composer install && cp .env.example .env` (if needed) — run migrations: `php artisan migrate`
2. Seed or create a product with `supplier_id` linked to a contractor’s supplier profile for contractor tests
3. `php artisan test --filter=InventoryTest`
4. `GET /api/v1/inventory` with admin Sanctum token
5. `PUT /api/v1/inventory/{product}/adjust` body `{"quantity_delta":10,"notes":"restock"}`
