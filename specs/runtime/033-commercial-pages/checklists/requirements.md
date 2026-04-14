# Requirements Checklist — Commercial Pages

> **Phase:** 07_FRONTEND_APPLICATION  
> **Generated:** 2026-04-14T08:59:00Z

## Scope Coverage

- [ ] All routes listed in `spec.md` exist as Nuxt pages
- [ ] Each page has loading / empty / error states
- [ ] Pagination implemented where applicable (orders, invoices, rfqs)
- [ ] RFQ create supports editable line items
- [ ] Supplier quote submit supports required validation and API submission
- [ ] Quote compare supports 2+ quotes with side-by-side comparison
- [ ] Checkout form validates required fields and triggers next-step navigation
- [ ] Order detail shows status timeline and status badges
- [ ] Invoice detail is print-friendly and stable

## Architecture & Boundaries

- [ ] No business logic in pages (use composables/stores)
- [ ] API calls only via existing API client composable
- [ ] No direct DB access from frontend
- [ ] RBAC enforced server-side; frontend guards are UX-only

## UI / Design System

- [ ] Nuxt UI components used consistently
- [ ] `DESIGN.md` visual language applied (shadow-as-border, typography)
- [ ] Accessible focus states present

## i18n / RTL

- [ ] Arabic RTL layout verified on all pages
- [ ] Arabic strings are translation-key based (no hardcoded user-facing text)
- [ ] Currency/date/number formatting follows locale conventions

## Testing

- [ ] Vitest unit tests cover VAT computation and order number formatting (if implemented in frontend utils)
- [ ] Playwright e2e tests cover checkout flow and invoice QR visibility (if present)
