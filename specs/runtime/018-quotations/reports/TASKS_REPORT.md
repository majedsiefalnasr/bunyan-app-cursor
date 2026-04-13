# Tasks Report — Quotations

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-13T09:57:10Z

## Task Summary

| Metric         | Value |
| -------------- | ----- |
| Total Tasks    | 25    |
| Parallelizable | 0     |
| Sequential     | 25    |
| HIGH Risk      | 6     |
| MEDIUM Risk    | 11    |
| LOW Risk       | 8     |

## Risk-Ranked Task View

### 🔴 HIGH Risk Tasks

| ID   | Description                                               | Risk Factor                       |
| ---- | --------------------------------------------------------- | --------------------------------- |
| T002 | Create RFQ/quotation migrations with constraints          | Migration correctness + rollbacks |
| T006 | Policies (ownership + contractor supplier_profile gating) | RBAC hard rule                    |
| T010 | Award flow + comparison builder                           | Transactionality + data integrity |
| T013 | Route registration under correct middleware               | RBAC + public exposure risk       |
| T016 | RFQ feature tests (RBAC matrix)                           | Regression prevention             |
| T017 | Quotation feature tests (deadline/award)                  | Regression prevention             |

### 🟡 MEDIUM Risk Tasks

| ID   | Description                    | Risk Factor                       |
| ---- | ------------------------------ | --------------------------------- |
| T003 | Models + relationships + casts | N+1 risk if mis-modeled           |
| T005 | Repositories                   | Query performance and correctness |
| T007 | RFQ requests                   | Validation edge cases             |
| T008 | Quotation requests             | Validation + numeric precision    |
| T009 | RFQ service                    | Workflow rule enforcement         |
| T011 | RFQ controller                 | Correct resource shaping          |
| T012 | RFQ quotation controller       | Correct access scoping            |
| T014 | API resources                  | Response contract consistency     |
| T018 | Customer pages                 | UX + RTL                          |
| T019 | Details/compare pages          | Table UX + overfetching           |
| T020 | Contractor pages               | Eligibility UX                    |

### 🟢 LOW Risk Tasks

| ID   | Description             | Risk Factor       |
| ---- | ----------------------- | ----------------- |
| T001 | Status enums            | Low blast radius  |
| T004 | Factories               | Test scaffolding  |
| T015 | Structured logging      | Observability     |
| T021 | UI components           | Mostly isolated   |
| T022 | Composables             | Thin wrappers     |
| T023 | Navigation entry points | Minor routing     |
| T024 | Frontend tests          | Limited surface   |
| T025 | Validation gate run     | Mostly mechanical |

## External Dependencies

| Task ID | Package/Library | Version | Purpose                                 |
| ------- | --------------- | ------- | --------------------------------------- |
| —       | —               | —       | No new third-party dependencies planned |

## High-Downstream-Impact Tasks

| Task ID | Description          | Downstream Impact                          |
| ------- | -------------------- | ------------------------------------------ |
| T010    | Award flow semantics | Affects future order creation stage        |
| T014    | API response shape   | Impacts frontend + downstream integrations |
