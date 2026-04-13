# Invoicing — Specification

> **Stage:** Invoicing  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Branch:** `spec/021-invoicing`  
> **Generated:** 2026-04-13T12:05:00Z  
> **Source:** `specs/phases/04_COMMERCIAL_LAYER/STAGE_21_INVOICING.md`

## Overview

Deliver **tax invoices** for the commercial layer: VAT-compliant totals, ZATCA-style QR payload (Phase-1 TLV base64), PDF export, REST APIs with RBAC, and Arabic-first Nuxt UI. Invoices link to **orders** when generated automatically, or stand alone when created manually. **Upstream** orders and payments exist; this stage does not process card capture.

## Personas & Roles

- **Customer (العميل):** list and view own invoices; download PDF; request email send for own invoices; create manual draft invoices for self (optional line items) when permitted.
- **Supplier / Contractor (المورّد):** list and view invoices where `supplier_id` matches their profile; PDF for those invoices.
- **Admin (الإدارة):** full list, void, mark paid (support), resend email, create manual invoices on behalf of users when needed.

## In Scope

### Backend

- Tables `invoices`, `invoice_items` per stage schema; forward-only migration with `down()`.
- `InvoiceStatus` enum: `draft`, `sent`, `paid`, `overdue`, `void`.
- `Invoice` / `InvoiceItem` models with relationships to `Order`, `User` (customer), `SupplierProfile`.
- `InvoiceRepository` for pagination, idempotency checks (one auto invoice per completed order).
- `InvoiceService`: allocate `INV-YYYYMMDD-XXXX` daily sequence; compute **15% Saudi VAT** on line subtotals; build ZATCA TLV payload and store base64 in `zatca_qr_data`; PDF generation via **Dompdf**; `void`, `markPaid`, `sendEmail` (queued or sync `Mail`).
- `InvoicePolicy` mirroring order visibility (customer owns `customer_id`; contractor matches `supplier_id`; admin all).
- Thin `InvoiceController` + Form Requests: index, store (manual), show, pdf (stream), send, void (PUT).
- Routes under `auth:sanctum`, `role:*` middleware, policies on mutations.
- **Auto-generation:** when an order transitions to `completed`, create invoice if none exists (same DB transaction as status change).

### Frontend

- `pages/invoices/index.vue` — role-scoped table (reuse dashboard layout patterns).
- `pages/invoices/[id].vue` — detail, totals, QR preview, print-friendly layout, actions (PDF, send where allowed).
- `pages/invoices/create.vue` — manual invoice form (customer-scoped defaults).
- Composable `useInvoices()` using `useApi` and Laravel envelope.
- i18n keys under `invoice.*`.

### Out of Scope

- Full ZATCA Fatoora platform integration, cryptographic signing, clearance APIs.
- Multi-currency beyond SAR display assumptions already used elsewhere.
- Credit notes / debit notes as separate documents.

## User Stories

### US1 — Auto invoice on order completion

As the system, when an order reaches **completed**, I persist one invoice with lines from order items, VAT 15%, and linked `order_id`.

### US2 — Customer lists invoices

As a customer, I can `GET /api/v1/invoices` and see only my invoices.

### US3 — Download PDF

As an authorized user, I can `GET /api/v1/invoices/{id}/pdf` and receive `application/pdf`.

### US4 — Void invoice

As an admin, I can `PUT /api/v1/invoices/{id}/void` to set status `void` with validation (e.g. not already void).

### US5 — Send by email

As an authorized user, I can `POST /api/v1/invoices/{id}/send` to email the customer a link or attachment per mail configuration.

### US6 — Manual invoice

As a customer or admin, I can `POST /api/v1/invoices` with line items and optional `order_id` (nullable) to create a **draft** invoice.

## Acceptance Criteria

- All invoice routes return the standard `{ success, data, message, errors }` envelope.
- VAT math: `vat_amount = round(subtotal * 0.15, 2)`; `total = round(subtotal + vat_amount, 2)`.
- Invoice numbers unique per day prefix rule.
- Feature tests: RBAC denial for cross-customer access; auto-create on order completion; PDF returns 200.

## Dependencies

- **Orders:** `Order`, `OrderItem`, `OrderService::transitionStatus` to `completed`.
- **Payments:** not required for invoice persistence; `paid_at` may be set manually or by future payment reconciliation.

## Clarifications

### Session 2026-04-13

- **Seller VAT / name for ZATCA QR:** Use `config/invoicing.php` with `INVOICING_SELLER_NAME` and `INVOICING_VAT_NUMBER` env placeholders (dummy VAT allowed in non-prod).
- **Email transport:** Use Laravel `Mail` with log driver acceptable in local; no third-party ESP.
- **Overdue:** Computed for display when `due_date < today` and status `sent`; optional cron out of scope — status remains `sent` until paid or voided unless admin marks overdue later.
