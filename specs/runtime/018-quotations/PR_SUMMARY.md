# Pull Request Summary — STAGE_18: Quotations (RFQs)

**Branch:** `spec/018-quotations`  
**Base:** `develop`  
**Status:** PRODUCTION READY  
**Date:** 2026-04-13

---

## Summary

Delivers the **Request for Quotation (RFQ)** and **quotation** commercial slice: customers create and send RFQs, invited suppliers submit quotes, customers compare offers, move to **evaluation**, **award** one quotation, then **close** the RFQ. Includes Laravel API + policies + tests, structured logging, and Nuxt UI for customers and contractors.

---

## Key behavior

- **Statuses:** `draft` → (send) → `sent` / `quoting` → `POST .../evaluate` → `evaluation` → (accept) → `awarded` → (close) → `closed`.
- **Targeting:** `rfq_targets` snapshot at send time; contractor access gated by target + supplier profile.
- **Atomic award:** Accept updates winner, rejects others, updates RFQ inside a DB transaction (rollback covered by unit test).
- **Rate limits:** Named limiters `rfq-send` and `rfq-quote` keyed by user + RFQ id.

---

## Backend (high level)

- New migration, enums, models (`Rfq`, `RfqItem`, `RfqTarget`, `Quotation`, `QuotationItem`), repositories, `RfqService`, `QuotationService`, policies, form requests, resources, `RfqController`, `RfqQuotationController`, routes under `auth:sanctum` + `role:` middleware.

## Frontend (high level)

- `composables/useRfqs.ts`, `types/rfq.ts`, `components/rfq/*`, pages under `pages/rfqs/**` and `pages/contractor/rfqs/**`, navigation + dashboard entry points, i18n (`ar` / `en`).

---

## How to test

See `specs/runtime/018-quotations/guides/TESTING_GUIDE.md`.

---

## Checklist for reviewers

- [ ] Migration applies cleanly on MySQL.
- [ ] RBAC: customer-only writes; contractor quote only when targeted; admin read patterns match product conventions.
- [ ] Award only from **evaluation** (evaluate step is intentional).
- [ ] Pre-push / CI: `composer run lint`, `composer run test`, frontend `lint`, `typecheck`, `test`.

---

## Related artifacts

- `specs/runtime/018-quotations/reports/IMPLEMENT_REPORT.md`
- `specs/runtime/018-quotations/audits/VALIDATION_REPORT.md`
- `specs/runtime/018-quotations/reports/CLOSURE_REPORT.md`
