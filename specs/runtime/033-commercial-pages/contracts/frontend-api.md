# Commercial Pages — Frontend API Contracts (Observed)

> **Source of truth**: existing composables under `frontend/composables/`  
> **Generated:** 2026-04-14T08:59:00Z

## Base

Composables use `useApi().apiFetch()` with `/v1/*` paths (API base configured in runtime config).

## RFQs

From `useRfqs()`:

- `GET /v1/rfqs`
- `GET /v1/rfqs/:id`
- `POST /v1/rfqs`
- `POST /v1/rfqs/:id/send`
- `POST /v1/rfqs/:id/evaluate`
- `POST /v1/rfqs/:id/close`
- `GET /v1/rfqs/:id/compare`
- `GET /v1/rfqs/:rfqId/quotations`
- `POST /v1/rfqs/:rfqId/quotations`
- `PUT /v1/rfqs/:rfqId/quotations/:quotationId/accept`

## Orders

From `useOrders()`:

- `GET /v1/orders`
- `GET /v1/orders/:id`
- `PUT /v1/orders/:id/confirm`
- `PUT /v1/orders/:id/cancel`

## Payments

From `usePayments()`:

- `GET /v1/payments/history`
- `GET /v1/payments/:id`
- `POST /v1/payments/initiate`

## Invoices

From `useInvoices()`:

- `GET /v1/invoices`
- `GET /v1/invoices/:id`
- `POST /v1/invoices`
- `POST /v1/invoices/:id/send`
- `GET /v1/invoices/:id/pdf` (expects `application/pdf`)
