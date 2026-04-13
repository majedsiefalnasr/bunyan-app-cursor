# Data Model — Orders

## orders

| Column                               | Type                          | Notes                    |
| ------------------------------------ | ----------------------------- | ------------------------ |
| id                                   | bigint PK                     |                          |
| customer_id                          | FK users                      |                          |
| supplier_id                          | FK supplier_profiles nullable | from quotation or manual |
| project_id                           | FK projects nullable          |                          |
| quotation_id                         | FK quotations nullable        | set when converted       |
| order_number                         | string unique                 | `BNY-YYYYMMDD-####`      |
| status                               | string                        | enum cast                |
| subtotal                             | decimal(15,2)                 | default 0                |
| tax_amount                           | decimal(15,2)                 | default 0                |
| shipping_amount                      | decimal(15,2)                 | default 0                |
| total_amount                         | decimal(15,2)                 | canonical grand total    |
| notes                                | text nullable                 |                          |
| delivery_date                        | date nullable                 | legacy                   |
| delivery_address                     | string nullable               | legacy                   |
| confirmed_at                         | datetime nullable             |                          |
| shipped_at                           | datetime nullable             |                          |
| delivered_at                         | datetime nullable             |                          |
| created_at / updated_at / deleted_at |                               | soft deletes             |

Indexes: `customer_id`, `supplier_id`, `quotation_id`, `status`, `order_number`.

## order_items

| Column      | Type            | Notes             |
| ----------- | --------------- | ----------------- |
| id          | bigint PK       |                   |
| order_id    | FK              | cascade           |
| product_id  | FK              |                   |
| variant_id  | FK nullable     |                   |
| quantity    | int             |                   |
| unit_price  | decimal         |                   |
| subtotal    | decimal         |                   |
| description | string nullable | optional snapshot |

## Relationships

- `Order` belongsTo `Quotation`, `SupplierProfile` (supplier), `User` (customer), `Project`.
- `Order` hasMany `OrderItem`.
- `Quotation` hasMany `Order` (optional inverse).
