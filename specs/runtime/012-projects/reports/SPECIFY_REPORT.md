# Specify Report — Projects

> **Phase:** 03_PROJECT_MANAGEMENT > **Generated:** 2026-04-12T11:30:00Z

## Specification Summary

| Metric                 | Value                                      |
| ---------------------- | ------------------------------------------ |
| User Stories           | 7                                          |
| Acceptance Criteria    | 4                                          |
| Technical Requirements | High                                       |
| Dependencies           | RBAC (STAGE_04), API foundation (STAGE_06) |
| Open Questions         | None (clarified in spec session block)     |

## Scope Defined

Projects module evolution: bilingual and geo fields, extended budgets, canonical status lifecycle, timeline read API, service/repository refactor, policy fixes for show, field-engineer visibility, and Nuxt dashboard pages for list/create/detail.

## Deferred Scope

Full Gantt UI, documents tab, dedicated team management beyond existing FKs, removing legacy single-language `name` column.

## Risk Assessment

HIGH: schema and enum migration touching core tables; mitigated by forward-only migration with explicit `UPDATE` mapping and comprehensive tests.

## Checklist Status

- Requirements checklist: Created at `checklists/requirements.md`
