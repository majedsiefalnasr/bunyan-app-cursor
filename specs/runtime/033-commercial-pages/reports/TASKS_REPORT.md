# Tasks Report — Commercial Pages

> **Phase:** 07_FRONTEND_APPLICATION > **Generated:** 2026-04-14T08:59:00Z

## Task Summary

| Metric         | Value |
| -------------- | ----- |
| Total Tasks    | 20    |
| Parallelizable | 8     |
| Sequential     | 12    |
| HIGH Risk      | 3     |
| MEDIUM Risk    | 9     |
| LOW Risk       | 8     |

## Risk-Ranked Task View

### 🔴 HIGH Risk Tasks

| ID   | Description                             | Risk Factor                            |
| ---- | --------------------------------------- | -------------------------------------- |
| T004 | `/payment/:orderId` compatibility route | Route collision / UX regression risk   |
| T015 | Invoice print layout + ZATCA QR         | Print CSS + RTL layout stability       |
| T019 | E2E coverage additions                  | Flaky tests / environment dependencies |

### 🟡 MEDIUM Risk Tasks

| ID   | Description                  | Risk Factor                        |
| ---- | ---------------------------- | ---------------------------------- |
| T006 | RFQ create validation + i18n | Schema validation + translations   |
| T007 | Supplier quote submit page   | Role-based UX, payload correctness |
| T009 | RFQ compare RTL-safe layout  | Table mirroring/scrolling in RTL   |
| T010 | Orders list pagination       | API pagination alignment           |
| T011 | Orders timeline              | Status mapping correctness         |
| T012 | Checkout initiation          | Payload correctness + UX states    |
| T013 | Payment detail               | Error handling + empty states      |
| T014 | Invoice list                 | Pagination + label mapping         |
| T017 | i18n keys additions          | Coverage across pages/components   |

### 🟢 LOW Risk Tasks

| ID   | Description                 | Risk Factor                  |
| ---- | --------------------------- | ---------------------------- |
| T001 | Spec route map alignment    | Documentation-only           |
| T002 | `/rfqs/create` alias        | Simple redirect              |
| T003 | `/checkout` alias           | Simple redirect              |
| T005 | Payment success page        | Mostly presentational        |
| T008 | RFQ detail states           | UX completeness              |
| T016 | Invoice PDF download button | Wiring to existing helper    |
| T018 | Unit tests additions        | Local-only, low blast radius |
| T020 | Run FE validation and fix   | Expected incremental fixes   |

## External Dependencies

| Task ID | Package/Library | Version | Purpose                                                      |
| ------- | --------------- | ------- | ------------------------------------------------------------ |
| —       | —               | —       | No new dependencies planned (reuse Nuxt UI + existing stack) |

## High-Downstream-Impact Tasks

| Task ID | Description                 | Downstream Impact                                   |
| ------- | --------------------------- | --------------------------------------------------- |
| T004    | Payment route compatibility | Affects navigation entry points from multiple areas |
| T017    | i18n keys                   | Affects all new commercial pages’ UI strings        |
