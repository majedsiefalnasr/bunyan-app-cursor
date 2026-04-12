# STAGE_25 — Activity Log

> **Phase:** 05_COMMUNICATION_AND_MEDIA
> **Status:** DRAFT
> **Scope:** User activity tracking, audit trail
> **Risk Level:** LOW

## Stage Status

Status: DRAFT
Step: plan
Risk Level: LOW
Last Updated: 2026-04-12T12:53:53Z

Scope Planned: Migration, service/repository, admin + subject APIs, trait on Project, prune command, admin UI + timeline component, PHPUnit coverage.

Deferred Scope: SIEM, websockets, legal hold, full export pipeline.

Architecture Governance Compliance: Technical plan compliant — task generation authorized

Notes: Technical plan complete. Task breakdown in progress.

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
