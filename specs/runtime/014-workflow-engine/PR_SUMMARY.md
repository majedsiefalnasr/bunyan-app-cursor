# PR — Workflow Engine

## Summary

**Stage:** Workflow Engine  
**Phase:** 03_PROJECT_MANAGEMENT  
**Branch:** `spec/014-workflow-engine` → `develop`  
**Tasks:** 11 / 11 completed

## What Changed

### Backend

- Added `WorkflowEngineService` and `WorkflowDefinitionService` with repositories for instances and approvals.
- New API routes: admin workflow definitions, `POST projects/{project}/workflow/start`, `GET approvals/pending`, `PUT workflow-instances/{id}/approve|reject`.
- Migrations extending `workflow_configurations` and creating `workflow_instances` / `workflow_approvals`.
- Feature tests in `WorkflowEngineTest` and schema coverage updates.

### Frontend

- New admin page `pages/admin/workflows.vue` and sidebar link; i18n keys under `workflow.*`.

### Database

- `2026_04_12_180000_add_workflow_engine_fields_to_workflow_configurations_table.php`
- `2026_04_12_180001_create_workflow_instances_table.php`
- `2026_04_12_180002_create_workflow_approvals_table.php`

## Breaking Changes

- None.

## Testing

- [x] Unit / feature suite (`composer run test`)
- [x] Frontend tests (`npm run test`)
- [x] Lint (`composer run lint`, `npm run lint`)
- [x] Type check (`npm run typecheck`; PHPStan via `composer run lint`)

## Checklist

- [x] RBAC middleware applied on all new routes
- [x] Form Request validation on new write endpoints
- [x] Arabic/RTL support via i18n keys
- [x] Error contract followed (`BaseController` / `ApiResponse`)
- [x] Eager loading on definition list and pending approvals

## Related

- Stage File: `specs/phases/03_PROJECT_MANAGEMENT/STAGE_14_WORKFLOW_ENGINE.md`
- Testing Guide: `specs/runtime/014-workflow-engine/guides/TESTING_GUIDE.md`
