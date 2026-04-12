# Specify Report — Tasks

> **Phase:** 03_PROJECT_MANAGEMENT > **Generated:** 2026-04-12T14:05:00Z

## Specification Summary

| Metric                 | Value                                                  |
| ---------------------- | ------------------------------------------------------ |
| User Stories           | 7                                                      |
| Acceptance Criteria    | 9                                                      |
| Technical Requirements | API, migrations, service/repository, policies, Nuxt UI |
| Dependencies           | STAGE_12_PROJECTS (projects, phases, RBAC)             |
| Open Questions         | Resolved in Clarify                                    |

## Scope Defined

Project-scoped task CRUD, assignment, validated status machine, comments, dependency MVP, unified `project_id` on `tasks`, bilingual titles, priorities, dashboard UI (list + Kanban-style).

## Deferred Scope

Full dependency cycle detection, websockets, Gantt.

## Risk Assessment

MEDIUM: schema migration on existing `tasks` + enum value migration; RBAC regression if routes mis-grouped.

## Checklist Status

- Requirements checklist: Created at `checklists/requirements.md`
