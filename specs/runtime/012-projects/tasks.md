# Tasks — Projects (STAGE_12)

- [ ] T001 [P] [US1] Add forward-only migration `backend/database/migrations/2026_04_12_120000_evolve_projects_for_stage_12.php` (columns + status remap + down).
- [ ] T002 [P] [US5] Add migration `backend/database/migrations/2026_04_12_120001_evolve_phases_for_stage_12.php` for `name_ar`, `name_en`, `sort_order`.
- [ ] T003 [US4] Replace `backend/app/Enums/ProjectStatus.php` with lifecycle enum and Arabic labels; update `backend/tests/Unit/Enums/ProjectStatusTest.php`.
- [ ] T004 [US1] Extend `backend/app/Models/Project.php` fillable/casts/scopes (`forUser` field engineer via reports); align `scopeActive` naming with `in_progress`.
- [ ] T005 [US5] Extend `backend/app/Models/Phase.php` fillable for new columns.
- [ ] T006 [US1] [US6] Implement `backend/app/Repositories/ProjectRepository.php` and `backend/app/Services/ProjectService.php` (pagination, CRUD, transitions, timeline).
- [ ] T007 [US1] [US4] Update `backend/app/Policies/ProjectPolicy.php` (`view` field engineer, `transitionStatus`).
- [ ] T008 [US2] [US3] Update `backend/app/Http/Requests/Api/V1/CreateProjectRequest.php` and `UpdateProjectRequest.php` (new fields; strip status from update).
- [ ] T009 [US4] Add `backend/app/Http/Requests/Api/V1/TransitionProjectStatusRequest.php`.
- [ ] T010 [US1] [US6] Refactor `backend/app/Http/Controllers/Api/V1/ProjectController.php` to use `ProjectService` and authorize `show`; add `status` + `timeline` actions.
- [ ] T011 [US1] Register routes in `backend/routes/api.php` for `PUT projects/{project}/status` and `GET projects/{project}/timeline` with correct RBAC middleware.
- [ ] T012 [US1] Update `backend/app/Http/Resources/Api/V1/ProjectResource.php` and `PhaseResource.php`; update `backend/database/factories/ProjectFactory.php`.
- [ ] T013 [US1] Update `backend/tests/Feature/Database/DatabaseSchemaTest.php`, `backend/tests/Feature/Database/EnumCastTest.php`, extend `backend/tests/Feature/ProjectControllerTest.php` (status, timeline, policy).
- [ ] T014 [US7] Add Nuxt pages `frontend/pages/projects/index.vue`, `frontend/pages/projects/new.vue`, `frontend/pages/projects/[id].vue` using `useApi` and Nuxt UI.
