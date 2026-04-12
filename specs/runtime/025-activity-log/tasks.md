# Tasks — Activity Log (STAGE_25)

- [ ] T001 [P] [US1] Add `backend/database/migrations/*_create_activity_logs_table.php` with indexes and `down()`.
- [ ] T002 [P] [US3] Add `backend/app/Enums/ActivityLogAction.php` for lifecycle actions.
- [ ] T003 [P] [US3] Add `backend/app/Models/ActivityLog.php` with morph subject + optional `actor` relation.
- [ ] T004 [US1] Add `backend/app/Repositories/ActivityLogRepository.php` for admin/subject queries + prune deletes.
- [ ] T005 [US1] [US3] [US4] Add `backend/app/Services/ActivityLogService.php` for record/paginate/prune + JSON sanitization.
- [ ] T006 [US3] Add `backend/app/Models/Concerns/LogsModelActivity.php` and apply to `backend/app/Models/Project.php`.
- [ ] T007 [US1] [US2] Add Form Requests + `backend/app/Http/Resources/Api/V1/ActivityLogResource.php`.
- [ ] T008 [US1] [US2] Add `backend/app/Http/Controllers/Api/V1/ActivityLogController.php` and wire routes in `backend/routes/api.php`.
- [ ] T009 [US4] Add `backend/config/activity_log.php` and `backend/app/Console/Commands/PruneActivityLogsCommand.php`.
- [ ] T010 [US1] [US2] [US3] Add `backend/tests/Feature/Api/V1/ActivityLogTest.php` covering RBAC + logging side-effects.
- [ ] T011 [US5] Add `frontend/pages/admin/activity-log.vue` (admin-only) listing `/v1/admin/activity-log`.
- [ ] T012 [US5] Add `frontend/components/dashboard/ActivityTimeline.vue` plus `frontend/locales/ar.json` + `frontend/locales/en.json` keys.
