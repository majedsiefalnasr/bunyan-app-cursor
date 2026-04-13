# Data Model — Invoicing

## Entity: Invoice

| Column         | Type       | Notes                               |
| -------------- | ---------- | ----------------------------------- |
| id             | bigint PK  |                                     |
| invoice_number | string     | Unique, `INV-YYYYMMDD-XXXX`         |
| order_id       | bigint? FK | Set when generated from order       |
| customer_id    | bigint FK  | Bill-to user                        |
| supplier_id    | bigint? FK | Mirrors order supplier when present |
| subtotal       | decimal    | Sum of line_subtotal                |
| vat_amount     | decimal    | 15% of subtotal unless overridden   |
| vat_percentage | decimal    | Default 15.00                       |
| total          | decimal    | subtotal + vat_amount               |
| status         | string     | draft, sent, paid, overdue, void    |
| due_date       | date?      |                                     |
| paid_at        | datetime?  |                                     |
| zatca_qr_data  | text?      | Base64 TLV payload                  |
| notes          | text?      |                                     |
| created_at     | datetime   |                                     |
| updated_at     | datetime   |                                     |
| deleted_at     | datetime?  | Soft delete                         |

## Entity: InvoiceItem

| Column         | Type      | Notes               |
| -------------- | --------- | ------------------- |
| id             | bigint PK |                     |
| invoice_id     | bigint FK |                     |
| description_ar | string?   |                     |
| description_en | string?   |                     |
| quantity       | int       | >= 1                |
| unit_price     | decimal   |                     |
| vat_rate       | decimal   | e.g. 15.00          |
| line_subtotal  | decimal   | qty \* unit_price   |
| line_vat       | decimal   |                     |
| line_total     | decimal   | line_subtotal + vat |

## Relationships

- `Invoice belongsTo Order` (optional)
- `Invoice belongsTo User as customer`
- `Invoice belongsTo SupplierProfile` (optional)
- `Invoice hasMany InvoiceItem`
