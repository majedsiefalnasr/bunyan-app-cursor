# Commercial Pages — Tasks

> **Generated:** 2026-04-14T08:59:00Z

- [x] T001 Update stage route map in spec to match existing canonical routes and planned aliases (`specs/runtime/033-commercial-pages/spec.md`)
- [x] T002 Add route alias/redirect page for `/rfqs/create` → `/rfqs/new` (`frontend/pages/rfqs/create.vue`)
- [x] T003 Add route alias/redirect page for `/checkout` → `/payments/checkout` (`frontend/pages/checkout.vue`)
- [x] T004 Implement `/payment/:orderId` compatibility route reusing existing payment detail UI (`frontend/pages/payment/[orderId].vue`)
- [x] T005 Implement payment success page with i18n keys and status/refetch (`frontend/pages/payment/success.vue`)
- [x] T006 Ensure RFQ create page uses Nuxt UI form + schema validation + i18n keys (`frontend/pages/rfqs/new.vue`)
- [x] T007 Add missing RFQ quote submit page `/rfqs/:id/quote` (supplier flow) using `useRfqs.submitQuotation` (`frontend/pages/rfqs/[id]/quote.vue`)
- [x] T008 Ensure RFQ detail page renders quotations list with pagination + empty/error/loading states (`frontend/pages/rfqs/[id]/index.vue`)
- [x] T009 Ensure RFQ compare page renders comparison matrix with RTL-safe table layout (`frontend/pages/rfqs/[id]/compare.vue`)
- [x] T010 Ensure orders list page uses pagination + status badge + i18n keys (`frontend/pages/orders/index.vue`)
- [x] T011 Ensure order detail page uses `UTimeline` with status timestamps and guarded actions (`frontend/pages/orders/[id].vue`)
- [x] T012 Ensure payments checkout page validates required fields and initiates payment via `usePayments.initiate` (`frontend/pages/payments/checkout.vue`)
- [x] T013 Ensure payments detail page displays method/status/reference and handles errors (`frontend/pages/payments/[id].vue`)
- [x] T014 Ensure invoices list page uses pagination + status label + i18n keys (`frontend/pages/invoices/index.vue`)
- [x] T015 Ensure invoice detail page renders VAT breakdown + conditional ZATCA QR and adds print CSS (`frontend/pages/invoices/[id].vue`)
- [x] T016 Add invoice PDF download button wiring via `useInvoices.downloadInvoicePdf` (`frontend/pages/invoices/[id].vue`)
- [x] T017 Add/extend i18n keys for all new/updated UI strings (`frontend/locales/ar.json`, `frontend/locales/en.json`)
- [x] T018 Add/extend unit tests for commercial UI helpers/components (VAT, comparison) (`frontend/tests/unit/**`)
- [x] T019 Add/extend Playwright e2e coverage for checkout/payment success + RFQ compare (`frontend/tests/e2e/**`)
- [x] T020 Run frontend lint/typecheck/tests and fix issues (`frontend/`)
