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
  - `per_page` max 50 (server-enforced)
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

Notes:

- On send, the system snapshots eligible suppliers into `rfq_targets`.
- After send, RFQ items become immutable.

### POST `/api/v1/rfqs/{rfq}/close`

- **Roles**: customer(owner)

### GET `/api/v1/rfqs/{rfq}/compare`

- **Roles**: customer(owner), admin
- Response is normalized and bounded:

- `items[]` (RFQ items)
- `quotations[]` (quotation summaries)
- `cells` map keyed by `quotation_id` → `rfq_item_id` → pricing

Caps:

- max RFQ items: 200
- max quotations compared: 50 (deterministic ordering: `total_price` asc, then `submitted_at` asc, then `id` asc)

## Quotations

### GET `/api/v1/rfqs/{rfq}/quotations`

- **Roles**:
  - customer(owner): sees all
  - contractor: sees own
  - admin: sees all
- **Query**:
  - `page` (default 1)
  - `per_page` (default 15)
  - `per_page` max 50 (server-enforced)

### POST `/api/v1/rfqs/{rfq}/quotations`

- **Roles**: contractor
- **Body**:
  - `valid_until?`, `delivery_days?`, `notes?`, `items[]` with `rfq_item_id`, `unit_price`, `notes?`

Notes:

- This endpoint is an **upsert**: if the contractor already has a quotation for this RFQ, it updates it (status becomes `REVISED`).
- Server computes `total_price` from items; client-sent totals are ignored.

### PUT `/api/v1/rfqs/{rfq}/quotations/{quotation}/accept`

- **Roles**: customer(owner)
