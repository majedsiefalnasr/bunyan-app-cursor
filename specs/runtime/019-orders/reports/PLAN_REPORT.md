# Plan Report — Orders

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-13T16:00:00Z

## Plan Summary

| Metric         | Value                                        |
| -------------- | -------------------------------------------- |
| New Tables     | 0 (extends existing)                         |
| New Endpoints  | 4 (confirm, cancel, status, quotation→order) |
| New Services   | 1 (`OrderService`)                           |
| New Pages      | 2 (`orders/index`, `orders/[id]`)            |
| New Components | 0 (inline Nuxt UI)                           |

## Architecture Decisions

- Centralize lifecycle and transitions in `OrderService`; persistence via `OrderRepository`.
- Extend `InventoryService` for reservation/release with row locks (default warehouse).
- Use `confirmed_at` as signal that inventory reservations exist and must be released on cancel.
- Admin-only generic `PUT /orders/{order}/status` to avoid privilege escalation.

## Guardian Verdicts

| Guardian              | Verdict | Notes                            |
| --------------------- | ------- | -------------------------------- |
| Architecture Guardian | PASS    | Layering preserved               |
| API Designer          | PASS    | Versioned REST + RBAC documented |

## Risk Assessment

| Risk Level | Count | Details                   |
| ---------- | ----- | ------------------------- |
| HIGH       | 1     | Inventory concurrency     |
| MEDIUM     | 1     | Quotation→order ownership |
| LOW        | 2     | i18n coverage, UI polish  |
