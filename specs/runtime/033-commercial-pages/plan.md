# Commercial Pages — Technical Plan

> **Phase:** 07_FRONTEND_APPLICATION  
> **Generated:** 2026-04-14T08:59:00Z

## Architecture Placement

- **Scope**: Frontend-only (Nuxt 3) pages + components + composables usage.
- **Boundaries**:
  - Pages must call APIs via composables (e.g. `useRfqs`, `useOrders`, `usePayments`, `useInvoices`)
  - No backend changes in this stage
  - RBAC is enforced by backend; frontend hides/guards actions for UX only

## Existing Building Blocks (Reuse First)

- **RFQs**
  - Pages: `frontend/pages/rfqs/index.vue`, `frontend/pages/rfqs/new.vue`, `frontend/pages/rfqs/[id]/index.vue`, `frontend/pages/rfqs/[id]/compare.vue`
  - Composable: `frontend/composables/useRfqs.ts`
  - Components: `frontend/components/rfq/*`
- **Orders**
  - Pages: `frontend/pages/orders/index.vue`, `frontend/pages/orders/[id].vue`
  - Composable: `frontend/composables/useOrders.ts`
- **Payments**
  - Pages: `frontend/pages/payments/index.vue`, `frontend/pages/payments/checkout.vue`, `frontend/pages/payments/[id].vue`
  - Composable: `frontend/composables/usePayments.ts`
- **Invoices**
  - Pages: `frontend/pages/invoices/index.vue`, `frontend/pages/invoices/[id].vue`, `frontend/pages/invoices/create.vue`
  - Composable: `frontend/composables/useInvoices.ts`

## Route Alignment Plan (Stage File vs Existing)

Stage file targets routes like `/checkout`, `/payment/:orderId`, `/payment/success`, `/rfqs/create`.

Implementation approach:

- **Keep existing canonical routes** where already present, and add **thin aliases/redirects** to match the stage file’s route expectations:
  - `/rfqs/create` → redirect to `/rfqs/new`
  - `/checkout` → redirect to `/payments/checkout`
  - `/payment/:orderId` → implement as alias to `/payments/:id` (or create new page that reuses the same view logic)
  - `/payment/success` → add new page (`frontend/pages/payment/success.vue` or `frontend/pages/payments/success.vue`) and optionally alias both

This keeps backwards compatibility while meeting spec routes.

## Data Contracts (Frontend)

See `contracts/frontend-api.md` for endpoint list and payload expectations based on existing composables.

## Page-by-Page Implementation Notes

### RFQs

- **List**: Use `useRfqs.listRfqs()` with query params; add `UPagination`.
- **Create**: Use `useRfqs.createRfq()`; editable line items table; validate via schema.
- **Detail**: Use `useRfqs.getRfq()` + `useRfqs.listQuotations()`.
- **Submit Quote (Supplier)**: Create page for `/rfqs/:id/quote` if missing; use `useRfqs.submitQuotation()`.
- **Compare**: Use `useRfqs.compareRfq()`; reuse `QuotationComparisonTable`.

### Orders

- **List**: `useOrders.listOrders()`; status badge; pagination.
- **Detail**: `useOrders.getOrder()`; show `UTimeline` (status + timestamps); allow confirm/cancel if API permits.

### Payments

- **Checkout**: Ensure `frontend/pages/payments/checkout.vue` uses `UForm` with schema validation and calls `usePayments.initiate()` (or existing checkout composable/store if present).
- **Payment Detail**: `frontend/pages/payments/[id].vue` should render method/status/reference.
- **Success Page**: Add explicit success page that reads order/payment reference from query params and re-fetches status where possible.

### Invoices

- **List**: `useInvoices.listInvoices()`; show status label; pagination.
- **Detail**: `useInvoices.getInvoice()`; show invoice header, items, VAT breakdown; render QR when `zatca_qr_data` exists.
- **PDF Download**: Use `useInvoices.downloadInvoicePdf()` and provide a button.
- **Print**: Add print CSS rules for stable layout.

## i18n / RTL Plan

- Add translation keys for any new UI strings in:
  - `frontend/locales/ar.json`
  - `frontend/locales/en.json`
- Ensure new pages respect `dir="rtl"` defaults and avoid LTR-only layouts.

## Testing Plan

- **Unit (Vitest)**:
  - VAT display helper (if implemented): correct 15% calculation/format
  - Order number formatting helper (if implemented) matches `BNY-YYYYMMDD-XXXX`
- **E2E (Playwright)**:
  - RFQ creation → quotation submission → compare page renders
  - Checkout → payment initiation → success page shows reference
  - Invoice detail shows QR when present (fixture/mocked response)

## Deliverables

- Spec artifacts: `plan.md`, `research.md`, `data-model.md`, `contracts/`, `quickstart.md`
- Code changes (Step 6): Nuxt pages/components updated or added, tests updated/added
