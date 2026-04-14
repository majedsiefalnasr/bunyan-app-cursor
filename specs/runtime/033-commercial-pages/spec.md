# Commercial Pages — Specification

> **Phase:** 07_FRONTEND_APPLICATION  
> **Stage File:** `specs/phases/07_FRONTEND_APPLICATION/STAGE_33_COMMERCIAL_PAGES.md`  
> **Generated:** 2026-04-14T08:59:00Z

## Overview

Implement Bunyan’s commercial/transactional frontend pages (RFQs, quotes, checkout, orders, payments, invoices) using **Nuxt 3 + Nuxt UI** with Arabic-first RTL UX, aligned with the Bunyan design system (`DESIGN.md`).

These pages are **frontend-only** deliverables and must integrate via existing REST API composables (no direct DB access; no business logic in pages).

## Goals

- Provide complete, navigable commercial user journeys for:
  - RFQ creation → supplier quote submission → buyer review/compare
  - Cart/checkout → payment → payment confirmation
  - Orders list/detail with status timeline
  - Invoices list/detail with print-ready layout
- Ensure UX works in Arabic RTL by default (layout, typography, number/date formatting).
- Use Nuxt UI components consistently and avoid bespoke UI primitives unless necessary.

## Non-Goals (Explicitly Out of Scope)

- Implementing backend endpoints, payment gateway integration, or invoice generation logic.
- Changing RBAC rules or creating new roles/permissions (frontend guards may be added for UX, but server-side remains authoritative).
- Building a full e-commerce cart state engine beyond what already exists (only UI + integration).

## Users & Roles (Frontend UX)

- **Customer (العميل)**:
  - Create RFQ, view RFQs, compare quotes, checkout, view orders/invoices
- **Supplier (المورّد)**:
  - View RFQs assigned/visible, submit quotes
- **Admin/Other roles**:
  - Not part of the primary flows unless existing navigation requires safe handling (hidden/disabled links)

> Server authorization is enforced by the API. Frontend route guarding is UX only.

## Routes (Pages)

| Page            | Route               | Primary Role(s)   |
| --------------- | ------------------- | ----------------- |
| RFQ Create      | `/rfqs/create`      | Customer          |
| RFQ Listing     | `/rfqs`             | Customer/Supplier |
| RFQ Detail      | `/rfqs/:id`         | Customer/Supplier |
| Quote Submit    | `/rfqs/:id/quote`   | Supplier          |
| Quote Compare   | `/rfqs/:id/compare` | Customer          |
| Order Listing   | `/orders`           | Customer          |
| Order Detail    | `/orders/:id`       | Customer          |
| Checkout        | `/checkout`         | Customer          |
| Payment         | `/payment/:orderId` | Customer          |
| Payment Success | `/payment/success`  | Customer          |
| Invoice Listing | `/invoices`         | Customer          |
| Invoice Detail  | `/invoices/:id`     | Customer          |

## UI Components (Nuxt UI)

All page UI must prefer these Nuxt UI components (or existing project equivalents):

- `USlideover`/`UPopover` for cart widget panel
- `UForm` + `UFormField` for forms (with schema validation)
- `URadioGroup` for payment method selection
- `UTimeline` for order status progression
- `UTable`, `UPagination`, `USkeleton`, `UAlert`, `UBadge`, `UCard`

## Functional Requirements

- **RFQs**
  - Create RFQ with editable line items table
  - List RFQs with role-scoped visibility (based on API)
  - Detail page shows RFQ details + received quotes list
  - Supplier quote submission page validates required fields and posts to API
  - Quote comparison shows side-by-side table for 2+ quotes
- **Checkout & Payments**
  - Checkout form with required address + contact fields (Arabic validation messages where applicable)
  - Payment method selector supports: card / bank transfer / mada
  - Payment page displays order info and next step instructions (API-driven status)
  - Payment success page shows order number and summary
- **Orders**
  - Orders list page (paginated)
  - Order detail page with status timeline and status badges
- **Invoices**
  - Invoice list page (paginated)
  - Invoice detail shows print-ready invoice document
  - Invoice document includes VAT (15%) display and a placeholder area for ZATCA QR (if provided by API)

## UX & i18n/RTL Requirements

- Default RTL layout; ensure tables/timelines/forms remain readable in RTL.
- Arabic-friendly formatting for:
  - Currency (SAR)
  - Dates (Hijri not required unless already supported)
  - Numbers (Arabic numerals based on existing locale settings)
- Empty states, loading states, and error states present on all pages.

## Error Handling Contract (Frontend)

- Use the project’s API client composable.
- Display API errors via `UAlert` and field-level form errors via `UForm`.
- Do not leak raw error objects to UI.

## Acceptance Criteria (High Level)

- All listed routes render without runtime errors and are wired to API composables.
- Each page has:
  - Loading state
  - Empty state
  - Error state
- RFQ → Quote → Compare flow works against mocked/stubbed API responses.
- Checkout form validates required fields and triggers payment flow navigation.
- Order detail shows a consistent status timeline for all known statuses.
- Invoice detail is print-friendly and visually stable.

## Dependencies

- Upstream stages: `STAGE_29_NUXT_SHELL` and existing commercial backend stages (17–21) per stage file.

## Clarifications

### Session 2026-04-14

1. **API contract source of truth**

- **Resolution**: Use existing frontend composables as the integration contract surface, backed by Laravel `/v1/*` routes (as implemented in `frontend/composables/useRfqs.ts`, `useOrders.ts`, `usePayments.ts`, `useInvoices.ts`). This stage should **not** invent new endpoints.
- **Impact**: Align page routes and payloads to existing composables; any missing endpoint becomes a separate backend stage.

2. **RFQ visibility and roles**

- **Resolution**: Support **Customer** and **Supplier** RFQ experiences, and preserve the existing **Contractor** RFQ area if already present (e.g. `frontend/pages/contractor/rfqs/*`). Final visibility is API-scoped; UI should render safely for each role.
- **Impact**: Pages should use role-aware navigation and guard actions (UX only).

3. **Payment flow behavior**

- **Resolution**: Implement an **instructional + API-driven** payment initiation flow (no real gateway integration): initiate via `POST /v1/payments/initiate` (see `usePayments.initiate`) and drive the UI from returned status/reference.
- **Impact**: Payment success page should reflect the resulting status, but gateway redirects are out of scope.

4. **Invoice requirements**

- **Resolution**: Include **VAT breakdown + ZATCA QR display** (when provided) as indicated by existing `InvoiceDetail.zatca_qr_data`.
- **Impact**: Invoice detail should render QR when present and remain printable.

5. **i18n scope**

- **Resolution**: Implement **Arabic-first with i18n-ready keys for all user-facing strings**, leveraging existing `frontend/locales/ar.json` and `frontend/locales/en.json`.
- **Impact**: Avoid hardcoded UI strings in new pages/components; wire translation keys.
