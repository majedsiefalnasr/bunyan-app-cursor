# PR — Team Management

## Summary

**Stage:** Team Management  
**Phase:** 03_PROJECT_MANAGEMENT  
**Branch:** `spec/015-team-management` → `develop`  
**Tasks:** 14 / 14 completed

## What Changed

### Backend

- Added `ProjectRole` enum, `ProjectMember` and `ProjectInvitation` models with activity logging.
- Added `ProjectMemberRepository`, `ProjectInvitationRepository`, and `ProjectTeamService` for list/add/invite/update/remove/accept flows.
- Added `ProjectTeamController` and `ProjectInvitationAcceptController` with Form Requests and API resources.
- Extended `ProjectPolicy` (`view` includes members, `manageTeam`) and `ProjectService` / `ProjectFactory` to ensure owner membership.
- Registered routes under `/api/v1` with appropriate `auth:sanctum`, `role`, and throttle middleware.

### Frontend

- Extended `pages/projects/[id].vue` with team card, member and invitation lists, and invite form for authorized roles.
- Added Arabic and English strings under `projects.team_*` and role labels.

### Database

- `2026_04_12_200000_create_project_members_and_project_invitations_tables.php` creates both tables, indexes, foreign keys, and backfills owner rows for existing projects.

## Breaking Changes

- None.

## Testing

- [x] Unit and feature tests pass (`composer run test` in `backend/`)
- [x] Frontend tests pass (`npm run test` in `frontend/`)
- [x] Lint passes (`composer run lint`, `npm run lint`)
- [x] Type check passes (`npm run typecheck`, `vendor/bin/phpstan analyse`)
- [ ] `php artisan migrate --pretend` against production-like MySQL (run in CI or with valid `.env`)

## Checklist

- [x] RBAC middleware applied on mutation routes; `view` enforced on read
- [x] Form Request validation on team and invitation accept paths
- [x] Arabic/RTL support via i18n keys and existing layout
- [x] Error contract followed (`BaseController` / `ApiResponse`)
- [x] Eager loading on team list queries
- [x] Migration includes `down()` rollback

## Related

- Stage File: `specs/phases/03_PROJECT_MANAGEMENT/STAGE_15_TEAM_MANAGEMENT.md`
- Testing Guide: `specs/runtime/015-team-management/guides/TESTING_GUIDE.md`
