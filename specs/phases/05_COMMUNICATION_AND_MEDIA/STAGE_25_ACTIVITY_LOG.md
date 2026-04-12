# STAGE_25 — Activity Log

> **Phase:** 05_COMMUNICATION_AND_MEDIA
> **Status:** DRAFT
> **Scope:** User activity tracking, audit trail
> **Risk Level:** LOW

## Stage Status

Status: BACKEND CLOSED
Step: implement
Risk Level: LOW
Last Updated: 2026-04-12T13:10:00Z

Scope Closed: Activity log table, admin index API, subject timeline API, Project lifecycle logging, prune command + config, admin Nuxt page, ActivityTimeline on project detail, tests and i18n.

Tasks: 12 / 12 completed

Implementation: COMPLETE

Deferred Scope: SIEM, websockets, legal hold, full export pipeline.

Architecture Governance Compliance: RBAC enforced; service/repository layering maintained; validation and static analysis passed in CI-equivalent commands.

Notes: Ready for closure and PR.

## Objective

Implement activity logging for audit trail and user activity tracking across the platform.

## Scope

### Backend

- Activity log model (who did what, when, to what)
- Activity log service (log automatically via model observers)
- Eloquent model trait for auto-logging changes
- Activity log filters (by user, entity, action, date range)
- Data retention policy (configurable)

### Frontend

- Activity log page (Admin)
- Activity timeline component (used in project/entity detail pages)
- Filter and search for activity entries

### API Endpoints

| Method | Route                          | Description              |
| ------ | ------------------------------ | ------------------------ |
| GET    | /api/v1/activity-log           | List activity entries    |
| GET    | /api/v1/{entity}/{id}/activity | Entity-specific activity |

### Database Schema

| Table         | Columns                                                                                                                                                       |
| ------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| activity_logs | id, user_id, action (created/updated/deleted/viewed/exported), subject_type, subject_id, properties_json (old/new values), ip_address, user_agent, created_at |

## Dependencies

- **Upstream:** STAGE_06_API_FOUNDATION
- **Downstream:** None (utility module)
