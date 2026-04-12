# Clarify Report — Pricing

> **Phase:** 02_CATALOG_AND_INVENTORY > **Generated:** 2026-04-12T17:12:00Z

## Clarification Summary

| Metric                | Value                    |
| --------------------- | ------------------------ |
| Questions Asked       | 4                        |
| Questions Resolved    | 4                        |
| Spec Sections Updated | Clarifications (spec.md) |

## Resolved Clarifications

| #   | Topic              | Resolution                                                              | Impact                          |
| --- | ------------------ | ----------------------------------------------------------------------- | ------------------------------- |
| 1   | Write API path     | Admin-only `PUT /api/v1/admin/products/{product}/pricing`               | RBAC aligned with catalog       |
| 2   | Supplier UI        | Admin `/admin/products/{id}/pricing` until supplier catalog RBAC exists | Scope narrowed                  |
| 3   | Variant resolution | Variant-specific tiers override; else `product.price + price_modifier`  | Calculator deterministic        |
| 4   | Price history      | Rows on `products.price` change only; tier edits via logs in this slice | Simpler `price_histories` model |

## Remaining Ambiguities

- None — all clarifications resolved.

## Checklists Generated

| Checklist    | Path                       | Items |
| ------------ | -------------------------- | ----- |
| Requirements | checklists/requirements.md | 9     |
| Security     | checklists/security.md     | 4     |
| Performance  | checklists/performance.md  | 3     |
