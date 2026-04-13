# Specify Report — Payments

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-13T12:05:00Z

## Specification Summary

| Metric                 | Value                                                             |
| ---------------------- | ----------------------------------------------------------------- |
| User Stories           | 7                                                                 |
| Acceptance Criteria    | 8                                                                 |
| Technical Requirements | RBAC, Form Requests, service/repository, sandbox gateway, webhook |
| Dependencies           | Orders, existing Transaction ledger (naming separation)           |
| Open Questions         | None (resolved in Clarifications session block)                   |

## Scope Defined

Checkout payments for orders; polymorphic schema ready for future payables; sandbox gateway; payment attempts audit trail; REST endpoints per stage; minimal Nuxt UI surfaces.

## Deferred Scope

Production Saudi gateways, invoicing PDFs, multi-currency, admin-only refund policy.

## Risk Assessment

**HIGH** financial domain — mitigated by sandbox-only gateway, audit logging, no PCI storage, policy checks, and explicit separation from ledger `transactions` table.

## Checklist Status

- Requirements checklist: Created at `checklists/requirements.md`
