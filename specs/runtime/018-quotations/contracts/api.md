# API Contract — Quotations

> All endpoints are under `/api/v1/` and return the Bunyan envelope:
> `{ success, data, message, errors }`
>
> The backend may include additional fields (for example, `error`) and clients should ignore unknown fields.

## RFQs

### GET `/api/v1/rfqs`

- **Auth**: `auth:sanctum`
- **Roles**: customer, contractor, admin
- **Query**:
  - `page` (default 1)
  - `per_page` (default 15)
  - `status` (optional; one of `DRAFT|SENT|QUOTING|EVALUATION|AWARDED|CLOSED`)
  - `sort` (optional; `created_at` / `-created_at`)

### POST `/api/v1/rfqs`

- **Roles**: customer
- **Body**:
  - `project_id?`, `title`, `description?`, `delivery_deadline?`, `response_deadline?`, `items[]`

### GET `/api/v1/rfqs/{rfq}`

- **Roles**: customer(owner), contractor(eligible), admin

### POST `/api/v1/rfqs/{rfq}/send`

- **Roles**: customer(owner)

### GET `/api/v1/rfqs/{rfq}/compare`

- **Roles**: customer(owner), admin

## Quotations

### GET `/api/v1/rfqs/{rfq}/quotations`

- **Roles**:
  - customer(owner): sees all
  - contractor: sees own
  - admin: sees all
- **Query**:
  - `page` (default 1)
  - `per_page` (default 15)

### POST `/api/v1/rfqs/{rfq}/quotations`

- **Roles**: contractor
- **Body**:
  - `valid_until?`, `delivery_days?`, `notes?`, `items[]` with `rfq_item_id`, `unit_price`, `notes?`

### PUT `/api/v1/rfqs/{rfq}/quotations/{quotation}/accept`

- **Roles**: customer(owner)
