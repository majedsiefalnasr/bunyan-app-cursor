# Specify Report — Invoicing

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-13T12:05:00Z

## Specification Summary

| Metric                 | Value                                    |
| ---------------------- | ---------------------------------------- |
| User Stories           | 6                                        |
| Acceptance Criteria    | 4                                        |
| Technical Requirements | High                                     |
| Dependencies           | Orders, config-based ZATCA seller fields |
| Open Questions         | None (clarified in spec session)         |

## Scope Defined

Invoices with items, SAR VAT 15%, ZATCA TLV QR payload, PDF, REST API with RBAC, auto-create on order completion, Nuxt list/detail/create, email send and void flows.

## Deferred Scope

Full ZATCA clearance, credit/debit notes, automated overdue cron.

## Risk Assessment

Medium: financial fields and PDF/email stack; mitigated by tests and config-driven seller identity.

## Checklist Status

- Requirements checklist: Created at `checklists/requirements.md`
