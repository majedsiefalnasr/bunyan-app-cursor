# Payments — Specification

> **Stage:** Payments  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Branch:** `spec/020-payments`  
> **Generated:** 2026-04-13T12:05:00Z  
> **Source:** `specs/phases/04_COMMERCIAL_LAYER/STAGE_20_PAYMENTS.md`

## Overview

Deliver a **checkout-oriented payment domain** for Bunyan: polymorphic payments against payables (v1: **orders** only), gateway abstraction with a **sandbox adapter**, auditable **payment attempts** (charge/refund/void), webhook ingestion, and customer-facing payment history. Arabic-first UX; **RBAC and Form Requests are mandatory** on every protected route.

**Naming note:** The platform already has a ledger `transactions` table (`App\Models\Transaction`). Gateway-level charge/refund rows for a payment live in a new table **`payment_attempts`** to avoid collision with the existing ledger model.

## Personas & Roles

- **Customer (العميل)**: initiates payment for own orders, views history, downloads receipt metadata.
- **Admin (الإدارة)**: read-only listing of payments for support (optional v1: same as customer scope expansion).

## In Scope (v1)

### Backend

- `payments` (polymorphic `payable` → `Order` in v1)
- `payment_attempts` (per gateway operation; JSON `gateway_response` without PCI data)
- Enums: payment status, method, attempt type/status
- `PaymentGatewayContract` + `SandboxPaymentGateway` implementation
- `PaymentService`: initiate, capture, refund; structured audit logging (`Log::channel('audit')` where configured)
- `PaymentRepository` / `PaymentAttemptRepository` (Eloquent only in repositories)
- Thin `PaymentController` + `PaymentWebhookController` (signed secret for sandbox)
- Policies: customer can act only on payments for orders they own
- Routes under `/api/v1/payments/*` plus `POST /api/v1/webhooks/payment` (non-Sanctum with shared secret)
- Optional: on `completed`, create/update ledger `Transaction` row linked to `order_id` (documented, idempotent)

### Frontend (Nuxt 3)

- Payment history list (`/payments`)
- Payment detail / confirmation (`/payments/[id]`)
- Checkout entry for an order: initiate flow (`/payments/checkout` with `orderId` query) using Nuxt UI + `useApi`

### API (aligned with stage doc; REST naming)

| Method | Route                                | Description                                           |
| ------ | ------------------------------------ | ----------------------------------------------------- |
| POST   | `/api/v1/payments/initiate`          | Start payment for payable                             |
| GET    | `/api/v1/payments/{payment}`         | Payment details                                       |
| POST   | `/api/v1/payments/{payment}/capture` | Capture authorized payment (sandbox: no-op authorize) |
| POST   | `/api/v1/payments/{payment}/refund`  | Refund completed payment (partial amount optional)    |
| GET    | `/api/v1/payments/history`           | Paginated history for current user                    |
| POST   | `/api/v1/webhooks/payment`           | Gateway callback (secret + payload validation)        |

## Out of Scope (Explicit)

- Live Moyasar/HyperPay/Tap credentials and production PCI flows (sandbox only in v1)
- Invoicing PDF layout beyond JSON receipt payload
- Multi-currency (SAR only)
- Payables other than `Order` until STAGE_21 extends polymorphic use

## User Stories

### US1 — Customer initiates payment

As a **customer**, I can initiate a payment for a **pending** order I own, choosing **mada**, **card**, or **bank_transfer** (bank path recorded as pending manual confirmation in sandbox).

### US2 — Customer views payment

As a **customer**, I can fetch a single payment and see status, amount, method, and attempts.

### US3 — Customer captures / completes sandbox flow

As a **customer**, I can call capture on a **processing** payment to transition to **completed** in sandbox.

### US4 — Customer requests refund

As a **customer**, I can refund a **completed** payment (admin-only refund rules deferred; v1 allows customer per sandbox policy).

### US5 — Webhook updates payment

As the **system**, I can accept a signed webhook payload that updates payment status and records an attempt (sandbox emits test events).

### US6 — Payment history

As a **customer**, I can list my payments with pagination.

### US7 — Frontend pages

As a **customer**, I can use RTL UI pages for history, detail, and checkout initiation.

## Acceptance Criteria

- All endpoints return the standard `{ success, data, message, errors }` / error contract used in Bunyan APIs.
- RBAC: only **customer** (and **admin** where explicitly allowed) hits mutating payment routes; history filtered by ownership.
- No card numbers or CVV stored or logged.
- Migrations forward-only with `down()`.
- Feature tests cover initiate, show, capture, refund, history, webhook secret failure/success.

## Dependencies

- **Orders** (`Order`, `OrderPolicy`) for payable and ownership.
- Upstream stages per phase doc; no blocking schema conflicts when using `payment_attempts` name.

## Clarifications

### Session 2026-04-13

- **Q: Table name for per-charge rows?**  
  **A:** Use `payment_attempts` (not `transactions`) to avoid clashing with the existing financial ledger `transactions` model.

- **Q: Production gateway?**  
  **A:** v1 ships **SandboxPaymentGateway** only; real providers plug in via the same contract later.

- **Q: Who may refund?**  
  **A:** v1: **customer** on own completed payments for sandbox parity with stage examples; tighten to admin in a follow-up if product requires.
