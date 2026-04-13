# Clarify Report — Orders

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-13T15:45:00Z

## Clarification Summary

| Metric                | Value                                      |
| --------------------- | ------------------------------------------ |
| Questions Asked       | 6                                          |
| Questions Resolved    | 6                                          |
| Spec Sections Updated | Clarifications table appended to `spec.md` |

## Resolved Clarifications

| #   | Topic                | Resolution                               | Impact                         |
| --- | -------------------- | ---------------------------------------- | ------------------------------ |
| 1   | Supplier visibility  | Filter orders by `supplier_id` = profile | Index + policy + controller    |
| 2   | Status route actor   | Admin-only generic status transition     | RBAC on `PUT .../status`       |
| 3   | Monetary columns     | Breakdown + `total_amount` canonical     | Migration + resource fields    |
| 4   | Inventory warehouse  | Default warehouse / variant rules        | `InventoryService` reservation |
| 5   | Quotation conversion | ACCEPTED + customer ownership            | `QuotationToOrderController`   |
| 6   | Refunded enum        | Reserved for payments                    | No fulfillment transitions     |

## Remaining Ambiguities

- None — all clarifications resolved.

## Checklists Generated

| Checklist     | Path                        | Items |
| ------------- | --------------------------- | ----- |
| Requirements  | checklists/requirements.md  | 12    |
| Security      | checklists/security.md      | 7     |
| Performance   | checklists/performance.md   | 3     |
| Accessibility | checklists/accessibility.md | 3     |
