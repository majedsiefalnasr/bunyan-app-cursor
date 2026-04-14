# Clarify Report — Commercial Pages

> **Phase:** 07_FRONTEND_APPLICATION > **Generated:** 2026-04-14T08:59:00Z

## Clarification Summary

| Metric                | Value               |
| --------------------- | ------------------- |
| Questions Asked       | 5                   |
| Questions Resolved    | 5                   |
| Spec Sections Updated | `## Clarifications` |

## Resolved Clarifications

| #   | Topic                        | Resolution                                                                                                  | Impact                                                                               |
| --- | ---------------------------- | ----------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------ |
| 1   | API contract source of truth | Use existing frontend composables + existing Laravel `/v1/*` routes; do not invent new endpoints            | Pages/payloads must align to current composables; backend gaps become separate stage |
| 2   | RFQ visibility and roles     | Support Customer + Supplier; preserve existing Contractor RFQ area where present; API remains authoritative | Role-aware navigation + guarded actions (UX)                                         |
| 3   | Payment flow behavior        | Instructional + API-driven initiation; no real gateway integration                                          | Use `usePayments.initiate`, status-driven UI                                         |
| 4   | Invoice requirements         | VAT breakdown + show ZATCA QR when provided                                                                 | Printable invoice UI, conditional QR                                                 |
| 5   | i18n scope                   | Arabic-first with i18n-ready translation keys (ar/en files exist)                                           | No hardcoded strings in new UI                                                       |

## Remaining Ambiguities

- None — all clarifications resolved.

## Checklists Generated

| Checklist    | Path                       | Items |
| ------------ | -------------------------- | ----- |
| Requirements | checklists/requirements.md | 22    |
