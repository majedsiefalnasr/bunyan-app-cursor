# Technical Plan — Team Management (STAGE_15)

## Architecture

- **Controllers:** `ProjectTeamController` (index/store/update/destroy) and `ProjectInvitationAcceptController` (accept) delegate to `ProjectTeamService`.
- **Services:** `ProjectTeamService` owns add/invite, role changes, removal safeguards, invitation hashing, and accept flow.
- **Repositories:** `ProjectMemberRepository`, `ProjectInvitationRepository` encapsulate queries and persistence.
- **Policies:** `ProjectPolicy` gains `manageTeam` and extends `view` for `project_members` participation.
- **Requests:** `StoreProjectTeamMemberRequest`, `UpdateProjectTeamMemberRequest`, `AcceptProjectInvitationRequest` (empty or authorize only).
- **Resources:** `ProjectMemberResource`, `ProjectInvitationResource` (plain token only on create response via controller merge).

## Migrations

1. `2026_04_12_200000_create_project_members_and_project_invitations_tables.php` — create tables, indexes, FK cascades, backfill owner members for existing projects.

## API Routes (`routes/api.php`)

- Inside `auth:sanctum`:
  - `GET projects/{project}/team` — any user passing `view` policy.
  - `POST|PUT|DELETE projects/{project}/team...` — `role:customer,contractor,supervising_architect,admin` + `manageTeam` authorize.
  - `POST invitations/{token}/accept` — authenticated; throttle; authorize via service/policy hook on invitation’s project `view` after resolve.

## Frontend

- Extend `pages/projects/[id].vue` with team card: fetch `GET /v1/projects/{id}/team`, render members and pending invitations; show invite form for roles that can manage (mirror backend roles in UI).

## Testing

- `ProjectTeamControllerTest` covering list, add user, invite email, update role, delete member, accept token, RBAC denials.

## Guardian pre-implementation

- **Architecture checker:** PASS — layering enforced; no business logic in controllers.
- **API designer:** PASS — versioned REST, standard error contract.

## Risks

LOW — additive tables; backfill must be idempotent (`firstOrCreate` per project/customer).
