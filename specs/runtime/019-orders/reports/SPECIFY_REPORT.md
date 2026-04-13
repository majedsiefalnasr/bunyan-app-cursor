# Specify Report — Orders

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-13T15:40:00Z

## Specification Summary

| Metric                 | Value                                          |
| ---------------------- | ---------------------------------------------- |
| User Stories           | 6                                              |
| Acceptance Criteria    | Embedded per story + status machine            |
| Technical Requirements | Service layer, migrations, RBAC, inventory     |
| Dependencies           | Products, inventory, RFQ/quotations            |
| Open Questions         | None in base spec (clarify step locks details) |

## Scope Defined

Order lifecycle, numbering, quotation conversion, inventory reservation, extended schema, APIs, and customer/supplier/admin UI for list and detail with Arabic-first UX.

## Deferred Scope

Payments, invoicing, multi-warehouse fulfillment, notification content.

## Risk Assessment

HIGH: financial-adjacent totals and inventory concurrency; mitigated by transactions, locks, and tests.

## Checklist Status

- Requirements checklist: Created at `checklists/requirements.md`
