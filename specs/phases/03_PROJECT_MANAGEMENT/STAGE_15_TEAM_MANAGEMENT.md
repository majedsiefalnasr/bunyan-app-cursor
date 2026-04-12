# STAGE_15 — Team Management

> **Phase:** 03_PROJECT_MANAGEMENT
> **Status:** DRAFT
> **Scope:** Project team composition, role assignment, invitations
> **Risk Level:** LOW

## Stage Status

Status: DRAFT
Step: tasks
Risk Level: LOW
Last Updated: 2026-04-12T15:48:00Z

Tasks Generated: Total: 14 atomic tasks

Architecture Governance Compliance: Task set compliant — drift analysis required

Notes: Analyze step pending.

## Objective

Implement team management for projects. Assign users to projects with specific project roles, manage invitations, and track team member activity.

## Scope

### Backend

- Project member model (user + project + project role)
- Project role enum (Owner, Manager, Engineer, Worker, Viewer)
- Team service (add, remove, change role, invite)
- Invitation model with token-based accept flow
- Team member activity tracking

### Frontend

- Team management panel (within project detail)
- Team member invitation form
- Team member role selector
- Team member list with role badges

### API Endpoints

| Method | Route                               | Description       |
| ------ | ----------------------------------- | ----------------- |
| GET    | /api/v1/projects/{id}/team          | List team members |
| POST   | /api/v1/projects/{id}/team          | Add/invite member |
| PUT    | /api/v1/projects/{id}/team/{userId} | Change role       |
| DELETE | /api/v1/projects/{id}/team/{userId} | Remove member     |
| POST   | /api/v1/invitations/{token}/accept  | Accept invitation |

### Database Schema

| Table               | Columns                                                                                     |
| ------------------- | ------------------------------------------------------------------------------------------- |
| project_members     | id, project_id, user_id, project_role, joined_at, created_at                                |
| project_invitations | id, project_id, email, project_role, token, invited_by, accepted_at, expires_at, created_at |

## Dependencies

- **Upstream:** STAGE_12_PROJECTS
- **Downstream:** STAGE_13_TASKS (team-scoped assignment)
