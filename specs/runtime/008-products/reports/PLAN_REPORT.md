# Plan Report — Products

> **Phase:** 02_CATALOG_AND_INVENTORY > **Generated:** 2026-04-12T13:12:00Z

## Plan Summary

| Metric         | Value                                                 |
| -------------- | ----------------------------------------------------- |
| New Tables     | 2 (`product_variants`, `product_media`) + 1 FK column |
| New Endpoints  | 2 nested admin POST routes                            |
| New Services   | 1 (`ProductService`)                                  |
| New Pages      | 2 (`/products`, `/products/[id]`)                     |
| New Components | 0 (inline cards in pages)                             |

## Architecture Decisions

- Retain `/api/v1/admin/products` prefix for mutations (clarified).
- Child gallery metadata in `product_media`; binary upload stays on `MediaController`.
- Repository owns filter/query graph; service owns orchestration and SKU generation.

## Guardian Verdicts

| Guardian              | Verdict | Notes                |
| --------------------- | ------- | -------------------- |
| Architecture Guardian | PASS    | Layering preserved   |
| API Designer          | PASS    | RBAC + Form Requests |

## Risk Assessment

| Risk Level | Count | Details                               |
| ---------- | ----- | ------------------------------------- |
| HIGH       | 0     |                                       |
| MEDIUM     | 1     | Schema migration on shared `products` |
| LOW        | 2     | New endpoints + Nuxt pages            |
