# Tasks — Quotations (STAGE_18)

> Generated: 2026-04-13T09:57:10Z

- [x] T001 Define RFQ/Quotation status enums at `backend/app/Enums/`
- [x] T002 Create a single migration for `rfqs`, `rfq_items`, `rfq_targets`, `quotations`, `quotation_items` in `backend/database/migrations/` (FK + indexes + down())
- [x] T003 Add Eloquent models `Rfq`, `RfqItem`, `Quotation`, `QuotationItem` in `backend/app/Models/` with relationships + casts
- [x] T004 Add factories for RFQ/quotation entities under `backend/database/factories/`

- [x] T005 Add repositories `RfqRepository`, `RfqItemRepository`, `QuotationRepository`, `QuotationItemRepository` under `backend/app/Repositories/`
- [x] T006 Add policies `RfqPolicy`, `QuotationPolicy` under `backend/app/Policies/` (customer ownership + contractor supplier_profile gating + rfq_targets snapshot)
- [x] T007 Add Form Requests for RFQ create and send under `backend/app/Http/Requests/Api/V1/` (authorize() via policies)
- [x] T008 Add Form Requests for quotation submit and accept under `backend/app/Http/Requests/Api/V1/`

- [x] T009 Implement `RfqService` under `backend/app/Services/` (create draft, send, status transitions; uses repositories)
- [x] T010 Implement `QuotationService` under `backend/app/Services/` (submit/revise upsert, accept award via `DB::transaction`, compare builder with caps)

- [x] T011 Implement `RfqController` under `backend/app/Http/Controllers/Api/V1/` (index/store/show/send/evaluate/close/compare)
- [x] T012 Implement `RfqQuotationController` under `backend/app/Http/Controllers/Api/V1/` (index/store/accept)
- [x] T013 Register routes in `backend/routes/api.php` under `auth:sanctum` + appropriate `role:` middleware (admin read-only; scope bindings for nested quotation routes)

- [x] T014 Add API Resources `RfqResource`, `QuotationResource` (and any comparison DTO/resource) under `backend/app/Http/Resources/Api/V1/`
- [x] T015 Add structured logging for key actions (created/sent/submitted/awarded) using existing logging conventions

- [x] T016 Add feature tests `backend/tests/Feature/Api/V1/RfqApiTest.php` (RBAC matrix incl admin write attempts + send/close transitions + pagination/sort + rate limit 429 + limiter keying isolation)
- [x] T017 Add feature tests `backend/tests/Feature/Api/V1/QuotationApiTest.php` (deadline enforcement 409 + upsert revise + own-quote access + award flow atomicity incl forced mid-transaction failure/rollback + mismatched rfq/quotation IDs + rate limit 429 + limiter keying isolation)

- [x] T018 Add new Nuxt pages for customer RFQs: `frontend/pages/rfqs/index.vue`, `frontend/pages/rfqs/new.vue`
- [x] T019 Add customer RFQ details + compare pages: `frontend/pages/rfqs/[id]/index.vue`, `frontend/pages/rfqs/[id]/compare.vue` (bounded compare UX)
- [x] T020 Add contractor RFQ inbox + quote page: `frontend/pages/contractor/rfqs/index.vue`, `frontend/pages/contractor/rfqs/[id].vue`

- [x] T021 Add RFQ UI components: `frontend/components/rfq/RfqStatusBadge.vue`, `frontend/components/rfq/QuotationComparisonTable.vue`
- [x] T022 Add composable API client wrappers for RFQs/quotations (e.g. `frontend/composables/useRfqs.ts`)
- [x] T023 Add navigation entry points from existing dashboard/pages to RFQs (customer + contractor UX)

- [x] T024 Add frontend tests for comparison table rendering + RTL basics (`frontend/tests/` with Vitest)
- [x] T025 Add unit tests for service rules (`backend/tests/Unit/`) and run full validation gate commands (lint/typecheck/tests/migrations)
