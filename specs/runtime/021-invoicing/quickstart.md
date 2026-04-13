# Quickstart — Invoicing

## Backend

```bash
cd backend
composer install
cp .env.example .env   # if needed
# Set INVOICING_SELLER_NAME, INVOICING_VAT_NUMBER in .env
php artisan migrate
php artisan test --filter=Invoice
```

## Frontend

```bash
cd frontend
npm install
npm run dev
```

Navigate to `/invoices` (authenticated customer or admin).

## Smoke API

1. Complete an order through admin status transitions to `completed`.
2. `GET /api/v1/invoices` — should include new invoice linked to order.
3. `GET /api/v1/invoices/{id}/pdf` — binary PDF.
