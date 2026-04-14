# Commercial Pages — Frontend Data Model

> **Generated:** 2026-04-14T08:59:00Z

## RFQ (Request for Quotation)

Source: `frontend/types/rfq.ts` + `frontend/composables/useRfqs.ts`

- **RfqDetail**
  - id, title, description, deadlines, items, status (as provided by API)
- **QuotationRow**
  - quotation metadata + item pricing rows (as provided by API)
- **ComparePayload**
  - comparison matrix for suppliers/quotes

## Order

Source: `frontend/composables/useOrders.ts`

- **OrderRow**
  - id, order_number, status, total_price, created_at
- **OrderDetail**
  - OrderRow + items[] + status timestamps (confirmed/shipped/delivered)

## Payment

Source: `frontend/composables/usePayments.ts`

- **PaymentRow**
  - payable_type/payable_id, amount, currency, method, status, gateway_reference, timestamps

## Invoice

Source: `frontend/composables/useInvoices.ts`

- **InvoiceRow**
  - invoice_number, status/status_label, totals, due_date, created_at
- **InvoiceDetail**
  - subtotal + vat_amount + vat_percentage + total + items[]
  - `zatca_qr_data` (string|null)
