# Specify Report — Cost Estimator

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-12T12:15:00Z

## Specification Summary

| Metric                 | Value                       |
| ---------------------- | --------------------------- |
| User Stories           | 9                           |
| Acceptance Criteria    | 5                           |
| Technical Requirements | Full stack (Laravel + Nuxt) |
| Dependencies           | Projects, Products, Pricing |
| Open Questions         | None locked for MVP         |

## Scope Defined

Project-scoped estimates with line items, calculation engine, approval workflow, multi-estimate comparison, CSV BOQ export, admin BOQ templates (CRUD + JSON storage), REST API with RBAC, feature tests, and Nuxt project UI.

## Deferred Scope

- PDF binary generation and styled XLSX
- One-click “apply BOQ template” to populate estimate lines
- Multi-currency and external labor rate integrations

## Risk Assessment

HIGH domain (financial totals) mitigated by server-side recalculation, decimal casting, policy checks, and explicit test coverage for totals and cross-tenant access.

## Checklist Status

- Requirements checklist: Created at `checklists/requirements.md`
