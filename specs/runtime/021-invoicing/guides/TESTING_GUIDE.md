# Testing Guide — Invoicing

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-13T22:25:00Z

## Prerequisites

```bash
cd backend && composer install && cp .env.example .env  # if needed
# Set DB credentials, then:
php artisan migrate
```

In `.env` add for realistic ZATCA TLV:

```env
INVOICING_SELLER_NAME="Bunyan Demo"
INVOICING_VAT_NUMBER="300000000000003"
```

```bash
cd frontend && npm install && npm run dev
```

## Automated Tests

```bash
cd backend && php artisan test --filter=InvoiceApiTest
cd backend && php artisan test
cd frontend && npm run test
cd frontend && npm run typecheck
```

## Manual Scenario 1 — Auto invoice on order completion

**Preconditions:** Admin user and customer user exist; MySQL running; migrations applied.

1. Sign in as **customer** and `POST /api/v1/orders` with at least one line item (or use UI if available).
2. Sign in as **admin** and advance the order through allowed statuses until `PUT /api/v1/orders/{id}/status` with body `{ "status": "completed" }` succeeds (path must be: pending → confirmed → processing → shipped → delivered → completed).
3. Sign in as **customer** and `GET /api/v1/invoices`. Expect at least one invoice with matching `order_id`.
4. `GET /api/v1/invoices/{id}/pdf` with the same auth; expect HTTP 200 and `Content-Type: application/pdf`.

## Manual Scenario 2 — Manual invoice (customer)

1. Sign in as **customer**.
2. `POST /api/v1/invoices` with body:

```json
{
  "items": [
    {
      "description_ar": "خدمة تركيب",
      "quantity": 1,
      "unit_price": 500.0
    }
  ]
}
```

3. Expect HTTP 201 and a new `invoice_number` like `INV-YYYYMMDD-XXXX`.

## Manual Scenario 3 — Void (admin)

1. Create any non-paid invoice (from Scenario 2).
2. As **admin**, `PUT /api/v1/invoices/{id}/void`.
3. Expect `status` = `void`.

## Manual Scenario 4 — Nuxt UI

1. Log in as **customer** in the browser.
2. Open `/ar/invoices` (or default locale path). List should load without console errors.
3. Open **Create**, submit one line, confirm redirect to detail page.
4. Click **Download PDF** and confirm a file downloads.

## Mail send

Ensure `MAIL_MAILER=log` (or a real transport) in `.env`, then as invoice owner call `POST /api/v1/invoices/{id}/send` and check `storage/logs/laravel.log` for mail output.
