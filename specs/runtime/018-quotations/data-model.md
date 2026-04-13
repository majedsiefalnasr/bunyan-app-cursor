# Data Model — Quotations

## rfqs

| Column               | Type                     | Notes                                   |
| -------------------- | ------------------------ | --------------------------------------- | ---- | ------- | ---------- | ------- | ------- |
| id                   | bigint PK                |                                         |
| project_id           | FK → projects nullable   | optional linkage                        |
| created_by           | FK → users               | customer owner                          |
| title                | string(255)              | required                                |
| description          | text nullable            |                                         |
| status               | string(32)               | `DRAFT                                  | SENT | QUOTING | EVALUATION | AWARDED | CLOSED` |
| delivery_deadline    | date nullable            | optional                                |
| response_deadline    | datetime nullable        | required on send (can be set at create) |
| sent_at              | datetime nullable        | set on send                             |
| awarded_quotation_id | FK → quotations nullable | set on award                            |
| awarded_at           | datetime nullable        | set on award                            |
| awarded_by           | FK → users nullable      | set on award                            |
| closed_at            | datetime nullable        | set on close                            |
| timestamps           |                          |                                         |

Indexes:

- `(created_by, status, created_at)`
- `(status, sent_at)`
- `(status, created_at)`
- `project_id`
- `awarded_quotation_id`

## rfq_items

| Column         | Type                   | Notes                        |
| -------------- | ---------------------- | ---------------------------- |
| id             | bigint PK              |                              |
| rfq_id         | FK → rfqs              | cascade delete               |
| product_id     | FK → products nullable | optional                     |
| description    | text                   | required (Arabic-first text) |
| quantity       | decimal(14,4)          | required                     |
| unit           | string(32)             | required                     |
| specifications | json nullable          | free structure               |
| sort_order     | unsigned int default 0 |                              |
| timestamps     |                        |                              |

Indexes:

- `rfq_id`
- `(rfq_id, sort_order)`
- `product_id`

## rfq_targets

Snapshot of eligible suppliers for an RFQ at send-time.

| Column      | Type                   | Notes                     |
| ----------- | ---------------------- | ------------------------- |
| id          | bigint PK              |                           |
| rfq_id      | FK → rfqs              | cascade delete            |
| supplier_id | FK → supplier_profiles | invited/eligible supplier |
| invited_at  | datetime               | set on send               |
| timestamps  |                        |                           |

Indexes & constraints:

- unique `(rfq_id, supplier_id)`
- index `(supplier_id, rfq_id)`

## quotations

| Column        | Type                   | Notes                       |
| ------------- | ---------------------- | --------------------------- | ------- | -------- | --------- |
| id            | bigint PK              |                             |
| rfq_id        | FK → rfqs              | cascade delete              |
| supplier_id   | FK → supplier_profiles | references supplier profile |
| status        | string(32)             | `SUBMITTED                  | REVISED | ACCEPTED | REJECTED` |
| total_price   | decimal(12,2)          | required                    |
| delivery_days | unsigned int nullable  | optional                    |
| notes         | text nullable          |                             |
| valid_until   | date nullable          |                             |
| submitted_at  | datetime nullable      | set on first submit         |
| timestamps    |                        |                             |

Indexes & constraints:

- unique `(rfq_id, supplier_id)` to enforce one quotation per supplier per RFQ
- `rfq_id`
- `supplier_id`
- `status`
- `(rfq_id, total_price, submitted_at, id)`

## quotation_items

| Column       | Type            | Notes          |
| ------------ | --------------- | -------------- |
| id           | bigint PK       |                |
| quotation_id | FK → quotations | cascade delete |
| rfq_item_id  | FK → rfq_items  |                |
| unit_price   | decimal(12,2)   | required       |
| total_price  | decimal(12,2)   | required       |
| notes        | text nullable   |                |
| timestamps   |                 |                |

Indexes:

- `quotation_id`
- `rfq_item_id`

## Relationships

- `Rfq belongsTo User (creator)`
- `Rfq belongsTo Project (optional)`
- `Rfq hasMany RfqItem`
- `Rfq hasMany Quotation`
- `Quotation belongsTo Rfq`
- `Quotation belongsTo SupplierProfile`
- `Quotation hasMany QuotationItem`
- `QuotationItem belongsTo Quotation`
- `QuotationItem belongsTo RfqItem`
