# Quotations — Specification

> **Stage:** Quotations  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Branch:** `spec/018-quotations`  
> **Generated:** 2026-04-13T09:43:25Z  
> **Source:** `specs/phases/04_COMMERCIAL_LAYER/STAGE_18_QUOTATIONS.md`

## Overview

Implement an RFQ (Request for Quotation) workflow enabling **customers** to request supplier quotations for a set of items, and enabling **suppliers** to submit, revise, and manage quotations. Customers can compare received quotations and award one quote, which downstream stages (orders) can consume.

Arabic-first UX (RTL) is required; server-side RBAC is mandatory on all endpoints.

## Personas & Roles

- **Customer (العميل)**: creates RFQs, sends to suppliers, views/compares quotations, awards a quotation, closes RFQ.
- **Supplier (المورّد)**: implemented as **Contractor role** (`contractor`) with an active `SupplierProfile`; receives RFQ invitations, submits/revises quotations, views its own submitted quotations.
- **Admin (الإدارة)**: can view all RFQs/quotations for support and compliance (**read-only in this stage**).

## In Scope

### Backend

- RFQ lifecycle and persistence
- RFQ items persistence
- Quotation submission lifecycle and persistence
- Quotation items persistence (per RFQ item)
- Supplier targeting (relevant suppliers by category / catalog linkage) **snapshotted at send time**
- Comparison endpoint (normalized comparison view)
- Award flow (accept one quotation; set others to rejected; record award metadata)
- Close RFQ (transition to `CLOSED`)
- Notifications/events hooks (trigger points defined; implementations can be async jobs)
- Strict error contract across all endpoints

### Frontend

- Customer RFQ create/edit draft, send, list/filter, details
- Supplier RFQ inbox/list (RFQs they can quote on), quotation submission & revision
- Customer quotation list & comparison table
- Award action UI (customer)

## Out of Scope (Explicit)

- Payments and invoicing for awarded quotation (handled by downstream orders/payment stages)
- Supplier onboarding/verification workflow (handled upstream)
- Multi-currency pricing (assume platform default currency; currency formatting handled via i18n layer)
- Complex optimization/auto-award (manual award only)

## User Stories

### US1 — Customer creates an RFQ draft

As a customer, I can create an RFQ with title/description, deadlines, and a list of items, and save it as **DRAFT**.

### US2 — Customer sends RFQ to suppliers

As a customer, I can send an RFQ, after which it becomes visible to relevant suppliers and transitions to **SENT/QUOTING**.

### US3 — Supplier submits a quotation

As a supplier, I can submit a quotation for an RFQ I’m eligible to quote on, including pricing per item and delivery terms.

### US4 — Supplier revises a quotation

As a supplier, I can revise my quotation before the response deadline. For this stage, the audit trail is **timestamps only** (`submitted_at`, `updated_at`).

### US5 — Customer compares quotations

As a customer, I can view a structured comparison of all received quotations for an RFQ.

### US6 — Customer awards a quotation

As a customer, I can award one quotation. The RFQ becomes **AWARDED**, the winning quotation becomes **ACCEPTED**, and others become **REJECTED**.

### US7 — Customer closes RFQ

As a customer, I can close an RFQ when it’s no longer active. No further quotations can be submitted.

## RFQ Status & Transitions

RFQ statuses (from stage file) and allowed transitions:

- **DRAFT** → **SENT** (send)
- **SENT** → **QUOTING** (system: suppliers notified / first eligible view)
- **QUOTING** → **EVALUATION** (system/customer: once response deadline reached or customer moves forward)
- **EVALUATION** → **AWARDED** (award)
- **AWARDED** → **CLOSED** (close)

Constraints:

- After **SENT**, RFQ items become immutable (except via a controlled “amend” flow which is out-of-scope).
- After **CLOSED**, no state changes allowed.

## Quotation Status & Rules

Quotation statuses:

- **SUBMITTED**
- **REVISED**
- **ACCEPTED**
- **REJECTED**

Constraints:

- Only one **ACCEPTED** quotation per RFQ.
- Supplier may only have one active quotation per RFQ (revisions update same quotation record, or create revision rows depending on repository pattern; see plan).
- Quotation submission/revision not allowed after RFQ response deadline or after RFQ leaves **QUOTING**.

## API Requirements

All routes are versioned under `/api/v1/` and protected by `auth:sanctum`, with policy checks per action.

Required endpoints (from stage file):

- `GET /api/v1/rfqs` — list RFQs (filtered by role visibility)
- `POST /api/v1/rfqs` — create RFQ (customer)
- `GET /api/v1/rfqs/{id}` — RFQ details (customer owner, invited supplier, admin)
- `POST /api/v1/rfqs/{id}/send` — send RFQ to suppliers (customer owner)
- `POST /api/v1/rfqs/{id}/close` — close RFQ (customer owner)
- `POST /api/v1/rfqs/{id}/quotations` — submit quotation (contractor with supplier profile)
- `GET /api/v1/rfqs/{id}/quotations` — list quotations (customer owner; supplier sees own; admin sees all)
- `PUT /api/v1/rfqs/{id}/quotations/{qid}/accept` — accept (award) quotation (customer owner)
- `GET /api/v1/rfqs/{id}/compare` — compare quotations (customer owner, admin read-only)

### Error Contract

All responses MUST include:

```json
{ "success": true, "data": {}, "message": "string", "errors": {} }
```

Error responses:

```json
{
  "success": false,
  "data": null,
  "message": "Error description",
  "errors": { "field": ["validation message"] }
}
```

Notes:

- Clients MUST rely on the presence of `success`, `data`, `message`, and `errors`.
- The backend may include additional fields (for example, an `error` object with codes/details) without breaking the contract.

### Localization

- API `message` values are **Arabic by default**, aligned with existing backend translation usage.
- Frontend UI copy is Arabic-first (RTL) and should not rely on English-only backend messages.

## Data Requirements (High-Level)

Minimum fields (aligned with stage file):

- **rfqs**: project linkage optional (nullable), created_by (customer), title, description, status, delivery_deadline, response_deadline
- **rfq_items**: rfq_id, product_id nullable, description, quantity, unit, specifications (free text or JSON)
- **quotations**: rfq_id, supplier_id, totals, delivery terms, notes, status, valid_until, submitted_at
- **quotation_items**: quotation_id, rfq_item_id, unit_price, total_price, notes

## Security & RBAC Requirements

- Policies enforce: customer can only act on their RFQs; suppliers only on eligible RFQs and their own quotations.
- Rate limits on send + submit endpoints to prevent abuse.
- Input validation via Form Requests everywhere.
- Money integrity: server computes and stores totals from quotation items; client-sent totals are ignored.

## Non-Functional Requirements

- **Arabic-first**: RTL layouts, Arabic labels/messages; formatting for dates and currency.
- **Performance**: avoid N+1 queries; always eager-load rfq items + quotations when needed; paginate lists.
- **Auditability**: record award action (who, when), quotation submission timestamps, and revision timestamps.

## Clarifications

### Session 2026-04-13

| #   | Topic                | Decision                                                                                                                                                                               | Rationale / Notes                                                 |
| --- | -------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------- |
| 1   | Supplier eligibility | RFQ eligibility is derived from RFQ items’ product categories (fallback to supplier catalog defaults). Eligible suppliers are **snapshotted** into `rfq_targets` when the RFQ is sent. | Prevents eligibility drift and keeps authorization deterministic. |
| 2   | Project linkage      | `project_id` is nullable on RFQ. If present, RFQ inherits project visibility rules for customer ownership.                                                                             | Supports both “general procurement” and project-bound RFQs.       |
| 3   | Deadline behavior    | `response_deadline` is authoritative for submit/revise; after it passes, supplier submit/revise is rejected server-side (409).                                                         | Prevents late quotes and simplifies comparisons.                  |
| 4   | Award semantics      | Awarding a quotation sets RFQ to `AWARDED`, winning quotation `ACCEPTED`, and all other quotations `REJECTED` in one DB transaction.                                                   | Financial-safety pattern; avoids partial state.                   |
| 5   | Supplier revisions   | Supplier revises by updating the same quotation record (status becomes `REVISED`) and overwriting items; revision timestamps tracked (`submitted_at`, `updated_at`).                   | Simple model; audit trail can be expanded later.                  |

**Ambiguities remaining**: None.
