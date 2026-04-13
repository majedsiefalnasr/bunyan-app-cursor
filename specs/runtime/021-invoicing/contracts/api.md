# API Contract — Invoices

> Base path `/api/v1/`. Envelope: `{ success, data, message, errors }`.

## GET `/invoices`

- **Auth:** `auth:sanctum`
- **Roles:** customer (own), contractor with supplier profile (supplier-linked), admin (all)
- **Query:** `page`, `per_page`, `status`

## POST `/invoices`

- **Roles:** customer (self as customer_id), admin
- **Body:** `order_id?`, `supplier_id?`, `due_date?`, `notes?`, `items[]` with `description_ar?`, `description_en?`, `quantity`, `unit_price`, optional `vat_rate` (default 15)

## GET `/invoices/{invoice}`

- **Roles:** customer owner, linked supplier, admin

## GET `/invoices/{invoice}/pdf`

- **Roles:** same as show
- **Response:** `application/pdf` stream

## POST `/invoices/{invoice}/send`

- **Roles:** customer owner, admin
- **Effect:** sends mailable to customer email; best-effort sets status to `sent` if was `draft`

## PUT `/invoices/{invoice}/void`

- **Roles:** admin
- **Effect:** status → `void` if not already void/paid
