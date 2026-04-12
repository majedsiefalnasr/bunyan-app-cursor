# Tasks — Team Management

- [x] T001 [P] [US1] Add migration `backend/database/migrations/2026_04_12_200000_create_project_members_and_project_invitations_tables.php` with backfill
- [x] T002 [P] [US1] Add `backend/app/Enums/ProjectRole.php`
- [x] T003 [P] [US1] Add models `backend/app/Models/ProjectMember.php`, `backend/app/Models/ProjectInvitation.php` with relationships + activity trait
- [x] T004 [US1] Add repositories `backend/app/Repositories/ProjectMemberRepository.php`, `backend/app/Repositories/ProjectInvitationRepository.php`
- [x] T005 [US2] [US3] [US4] [US5] Implement `backend/app/Services/ProjectTeamService.php`
- [x] T006 [US1] Extend `backend/app/Models/Project.php` with `members()` and `invitations()`
- [x] T007 [US1] [US2] [US3] [US4] Extend `backend/app/Policies/ProjectPolicy.php` (`view`, `manageTeam`, member safeguards)
- [x] T008 [US2] [US3] [US4] Add Form Requests under `backend/app/Http/Requests/Api/V1/`
- [x] T009 [US1] [US2] Add API resources `backend/app/Http/Resources/Api/V1/ProjectMemberResource.php`, `ProjectInvitationResource.php`
- [x] T010 [US1] [US2] [US3] [US4] [US5] Add controllers `backend/app/Http/Controllers/Api/V1/ProjectTeamController.php`, `ProjectInvitationAcceptController.php` and wire `backend/routes/api.php`
- [x] T011 [US1] Update `backend/app/Services/ProjectService.php` to create owner `ProjectMember` on project create
- [x] T012 [US1] [US2] [US3] [US4] [US5] Add `backend/tests/Feature/Api/V1/ProjectTeamControllerTest.php`
- [x] T013 [US6] Extend `frontend/pages/projects/[id].vue` for team panel
- [x] T014 [US6] Add i18n keys in `frontend/i18n/locales/ar.json` and `frontend/i18n/locales/en.json`
