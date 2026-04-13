# Plan Report — Payments

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-13T12:15:00Z

## Plan Summary

| Metric         | Value                                                   |
| -------------- | ------------------------------------------------------- |
| New Tables     | 2 (`payments`, `payment_attempts`)                      |
| New Endpoints  | 6 (initiate, history, show, capture, refund, webhook)   |
| New Services   | 1 (`PaymentService`) + gateway                          |
| New Pages      | 3 (`/payments`, `/payments/[id]`, `/payments/checkout`) |
| New Components | 0 (inline Nuxt UI pages)                                |

## Architecture Decisions

- Separate `payment_attempts` from ledger `transactions` to avoid naming/model collision.
- Sandbox-only gateway behind `PaymentGatewayContract`.
- Webhook authenticated via shared secret header (no Sanctum).
- v1 payable limited to `Order`; polymorphic schema for forward compatibility.

## Guardian Verdicts

| Guardian              | Verdict | Notes                                   |
| --------------------- | ------- | --------------------------------------- |
| Architecture Guardian | PASS    | Layering, RBAC, repositories respected  |
| API Designer          | PASS    | Versioned `/api/v1`, standard envelopes |

## Risk Assessment

| Risk Level | Count | Details                                     |
| ---------- | ----- | ------------------------------------------- |
| HIGH       | 1     | Financial flows — mitigated sandbox-only    |
| MEDIUM     | 1     | Webhook abuse — mitigated secret + throttle |
| LOW        | 2     | Pagination, eager-load discipline           |
