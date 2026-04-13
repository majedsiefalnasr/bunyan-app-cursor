# Tasks Report — Orders

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-13T16:05:00Z

## Task Summary

| Metric         | Value                                 |
| -------------- | ------------------------------------- |
| Total Tasks    | 18                                    |
| Parallelizable | 1 (T001 isolated migration authoring) |
| Sequential     | 17                                    |
| HIGH Risk      | 3                                     |
| MEDIUM Risk    | 8                                     |
| LOW Risk       | 7                                     |

## Risk-Ranked Task View

### 🔴 HIGH Risk Tasks

| ID   | Description                       | Risk Factor        |
| ---- | --------------------------------- | ------------------ |
| T001 | Schema migration + backfill       | Data + lock risk   |
| T006 | Inventory reserve/release         | Concurrency        |
| T007 | OrderService transitions + totals | Business integrity |

### 🟡 MEDIUM Risk Tasks

| ID   | Description                    | Risk Factor       |
| ---- | ------------------------------ | ----------------- |
| T005 | Repository scoping + numbering | Query correctness |
| T008 | Controller refactor            | Regression        |
| T009 | Quotation conversion           | Authorization     |
| T011 | Policy matrix                  | RBAC holes        |
| T012 | Route wiring                   | Exposure          |
| T014 | Feature tests                  | Coverage gaps     |
| T016 | Order UI pages                 | UX / API drift    |
| T017 | i18n                           | Missing keys      |

### 🟢 LOW Risk Tasks

| ID   | Description            |
| ---- | ---------------------- |
| T002 | Enum extension         |
| T003 | Model relations        |
| T004 | OrderItem columns      |
| T010 | Form request           |
| T013 | API resources          |
| T015 | Composable             |
| T018 | Checklist housekeeping |

## External Dependencies

| Task ID | Package/Library | Version | Purpose                        |
| ------- | --------------- | ------- | ------------------------------ |
| —       | Laravel         | 11.x    | Migrations, Sanctum, resources |

## High-Downstream-Impact Tasks

| Task ID | Description  | Downstream Impact       |
| ------- | ------------ | ----------------------- |
| T007    | OrderService | Payments/invoicing hook |
