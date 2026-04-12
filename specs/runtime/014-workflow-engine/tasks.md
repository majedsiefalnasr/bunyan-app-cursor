# Tasks — Workflow Engine

- [ ] T001 [US1] Add migration `backend/database/migrations/2026_04_12_180000_add_workflow_engine_fields_to_workflow_configurations_table.php` for `type`, `name_ar`, `name_en`, `is_active`
- [ ] T002 [US1] Add migration `backend/database/migrations/2026_04_12_180001_create_workflow_instances_table.php`
- [ ] T003 [US1] Add migration `backend/database/migrations/2026_04_12_180002_create_workflow_approvals_table.php`
- [ ] T004 [US1] Add enums `WorkflowInstanceStatus`, `WorkflowApprovalAction` and models `WorkflowInstance`, `WorkflowApproval` under `backend/app/Models/`
- [ ] T005 [US1] Add repositories `WorkflowInstanceRepository`, `WorkflowApprovalRepository`; extend `WorkflowConfigurationRepository` with `paginateAll`
- [ ] T006 [US1] Add `WorkflowDefinitionService`, Form Requests, API Resources, `WorkflowDefinitionController` at `backend/app/Http/Controllers/Api/V1/WorkflowDefinitionController.php`
- [ ] T007 [US3] Add `WorkflowEngineService` at `backend/app/Services/WorkflowEngineService.php` (start, pending, approve, reject)
- [ ] T008 [US3] Add `WorkflowInstancePolicy`, `ProjectWorkflowController`, `PendingApprovalController`, `WorkflowInstanceActionController`, resolve requests under `backend/app/Http/`
- [ ] T009 [US1] Register routes in `backend/routes/api.php` for workflows, project start, pending, approve/reject
- [ ] T010 [US1] Add `backend/tests/Feature/Api/WorkflowEngineTest.php` covering RBAC, start, duplicate, pending, approve
- [ ] T011 [US6] Add `frontend/pages/admin/workflows.vue`, keys in `frontend/i18n/locales/ar.json` and `en.json`, link in `frontend/layouts/admin.vue`
