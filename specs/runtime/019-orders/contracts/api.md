# API Contract — Orders

> Base path `/api/v1/`. Envelope: `{ success, data, message, errors }`.

## GET `/orders`

- **Auth:** `auth:sanctum`
- **Roles:** customer (own), contractor with supplier profile (supplier orders), admin (all)
- **Query:** `page`, `per_page`, `status`

## POST `/orders`

- **Roles:** customer, admin
- **Body:** `project_id?`, `items[]` with `product_id`, `quantity`, `price` (unit), optional `variant_id`

## GET `/orders/{order}`

- **Roles:** customer owner, linked supplier, admin

## PUT `/orders/{order}/confirm`

- **Roles:** customer owner, admin
- **Effect:** `pending` → `confirmed`, inventory reserve, `confirmed_at`

## PUT `/orders/{order}/cancel`

- **Roles:** customer owner, admin
- **Effect:** pending/confirmed → `cancelled`, release reservations

## PUT `/orders/{order}/status`

- **Roles:** admin
- **Body:** `{ "status": "processing|shipped|delivered|completed|..." }` must follow transition matrix

## POST `/quotations/{quotation}/to-order`

- **Roles:** customer (RFQ owner), admin
- **Preconditions:** quotation `ACCEPTED`, RFQ owned by caller
- **Effect:** creates `pending` order with lines from quotation items
