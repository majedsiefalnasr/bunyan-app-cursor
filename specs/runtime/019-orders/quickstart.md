# Quickstart — Orders

1. Run migrations: `cd backend && php artisan migrate`
2. Seed a customer user and supplier profile if needed (`php artisan db:seed`).
3. Create inventory lines for products under test (`warehouse_location=default`).
4. `POST /api/v1/orders` as customer with `items[]`.
5. `PUT /api/v1/orders/{id}/confirm` — verify `reserved_quantity` increases.
6. `PUT /api/v1/orders/{id}/cancel` — verify reservation released.

Frontend: visit `/orders` after login as customer.
