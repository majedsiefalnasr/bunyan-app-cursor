# Specification — Team Management (STAGE_15)

**Phase:** 03_PROJECT_MANAGEMENT  
**Authority:** `specs/phases/03_PROJECT_MANAGEMENT/STAGE_15_TEAM_MANAGEMENT.md`

## Summary

Deliver production-ready **project team management**: `project_members` and `project_invitations` tables, `ProjectRole` enum (owner, manager, engineer, worker, viewer), REST API under `/api/v1/projects/{project}/team` and invitation acceptance, service and repository layers, policies and Form Requests, activity logging for member lifecycle, and a Nuxt team panel on the project detail page with Arabic-first RTL and the Bunyan error contract.

## User stories

1. **US1 — List team**  
   As a user who can view the project, I can list current members and pending invitations.

2. **US2 — Add or invite**  
   As a customer owner, contractor, supervising architect, or admin, I can add an existing user by `user_id` or invite by `email` with a project role.

3. **US3 — Change role**  
   As an authorized manager, I can change another member’s project role within allowed rules.

4. **US4 — Remove member**  
   As an authorized manager, I can remove a member who is not the sole canonical owner row.

5. **US5 — Accept invitation**  
   As an authenticated user whose email matches the invitation, I can accept using the token and join the project.

6. **US6 — Dashboard UI**  
   As a stakeholder, I can see the team list and invite form on the project detail page when I have access.

## Functional requirements

### Backend

- **Migrations (forward-only):** `project_members` (`project_id`, `user_id`, `project_role`, `joined_at`, timestamps) with unique `(project_id, user_id)`; `project_invitations` (`project_id`, `email`, `project_role`, `token_hash`, `invited_by`, `accepted_at`, `expires_at`, timestamps). Backfill: each existing `projects` row gets a `project_members` row for `customer_id` with role `owner`.
- **Enum:** `ProjectRole` backed by string values matching API.
- **Layers:** `ProjectMemberRepository`, `ProjectInvitationRepository`, `ProjectTeamService`, thin controllers.
- **Authorization:** extend `ProjectPolicy::view` to include users present in `project_members`. New abilities: `manageTeam`, `removeMember`, `updateMemberRole` (same actor matrix as team mutations). Block removal or demotion of the canonical owner (`customer_id` user with role `owner`).
- **Endpoints (Sanctum + RBAC):**
  - `GET /api/v1/projects/{project}/team` — viewers (policy `view`).
  - `POST /api/v1/projects/{project}/team` — `manageTeam`; body supports `user_id` XOR `email` + `project_role`.
  - `PUT /api/v1/projects/{project}/team/{user}` — `manageTeam`; body `project_role`.
  - `DELETE /api/v1/projects/{project}/team/{user}` — `manageTeam`; owner safeguards.
  - `POST /api/v1/invitations/{token}/accept` — authenticated; email match; not expired; idempotent if already accepted.
- **Activity:** `ProjectMember` and `ProjectInvitation` use `LogsModelActivity` where applicable (created/updated/deleted).

### Frontend

- Extend `pages/projects/[id].vue` with a team `UCard`: table of members (name, email, role badge), pending invitations, and invite form (`email`, `project_role`, submit) when `manageTeam` is implied by role (UI mirrors backend matrix: customer, contractor, supervising_architect, admin).

### Non-goals

- Email delivery integration (invitation returns pending record; no SMTP in this stage).
- Fine-grained per-role permission matrix beyond documented rules.

## Acceptance criteria

- All team endpoints enforce Sanctum + policy; unauthorized users receive 403; IDOR prevented via `ProjectPolicy`.
- PHPUnit feature tests cover list/add/update/remove/accept for representative roles.
- `composer run lint` / `composer run test` and frontend `npm run lint`, `npm run typecheck`, `npm run test` pass.

## Clarifications

### Session 2026-04-12

- **Canonical owner:** `projects.customer_id` remains the business owner; they receive an explicit `project_members` row with `owner` on create and backfill.
- **Invitation token:** store `token_hash` only; acceptance endpoint receives raw token once (plaintext never persisted).
- **Team managers:** customer (owner), assigned contractor, supervising architect, and admin may mutate team membership.
- **Field engineer:** may view team only if they can `view` the project (existing report-based rule unchanged).
