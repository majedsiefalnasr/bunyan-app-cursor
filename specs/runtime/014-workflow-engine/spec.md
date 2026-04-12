# Specification — Workflow Engine (STAGE_14)

**Phase:** 03_PROJECT_MANAGEMENT  
**Authority:** `specs/phases/03_PROJECT_MANAGEMENT/STAGE_14_WORKFLOW_ENGINE.md`

## Summary

Deliver a **workflow engine** surface on top of existing persistence (`workflow_configurations`, `approval_rules`) plus new **execution** tables (`workflow_instances`, `workflow_approvals`). Provide versioned REST endpoints for admin definition management, starting a workflow on a **project**, listing **pending approvals** for the authenticated user’s role, and **approve / reject** actions with notes. Include an **admin Nuxt** page to list definitions and a minimal RTL-aligned UI. All routes use **Sanctum + RBAC**, **Form Requests**, **services + repositories**, and the Bunyan **API error contract**.

## User stories

1. **US1 — Definitions (admin)**  
   As an admin, I can list and inspect workflow configurations (bilingual display names, type, global vs project-scoped).

2. **US2 — Create definition (admin)**  
   As an admin, I can create a workflow configuration with JSON transition metadata and optional project scope.

3. **US3 — Start instance**  
   As a project stakeholder (customer, contractor, supervising architect, or admin), I can start a workflow instance for a project when a configuration exists.

4. **US4 — Pending approvals**  
   As a user whose role matches a pending approval row, I can list items awaiting my action.

5. **US5 — Approve / reject**  
   As an authorized approver, I can approve or reject a pending step with optional notes; server records actor and timestamp.

6. **US6 — Admin UI**  
   As an admin, I can open an Arabic-ready admin page that lists workflow definitions from the API.

## Functional requirements

### Data model alignment

- **Definitions:** Public API resource name `workflows` maps to the existing `workflow_configurations` table (not a duplicate `workflow_definitions` table). Add forward-only columns: `type` (string, matches `WorkflowType` enum), `name_ar`, `name_en`, `is_active` (boolean, default true) where missing from schema.
- **Steps / rules:** Ordered approval gates remain `approval_rules` rows linked to `workflow_configuration_id` (conceptual “workflow steps” from the stage brief).
- **Execution:** New `workflow_instances` (morph to `workflowable`, FK to `workflow_configuration_id`, `status`, timestamps) and `workflow_approvals` (`workflow_instance_id`, `approval_rule_id` nullable, `approver_role`, `action`, `notes`, `acted_by`, `acted_at`).

### Backend

- **Layers:** Repositories for new models; `WorkflowDefinitionService` (admin CRUD on configurations), `WorkflowEngineService` (start, pending list, approve, reject); thin controllers.
- **Routes (under `/api/v1/`, Sanctum):**
  - `GET/POST /workflows`, `GET /workflows/{workflowConfiguration}` — `role:admin`.
  - `POST /projects/{project}/workflow/start` — stakeholder per `ProjectPolicy::view` (or dedicated `startWorkflow` gate); idempotent guard if an open instance already exists (return existing or 422 — documented as 422 conflict).
  - `GET /approvals/pending` — authenticated; returns rows where `approver_role` matches the user’s `UserRole` value and `action = pending`.
  - `PUT /workflow-instances/{workflowInstance}/approve` and `.../reject` — approver role match or admin; Form Request validates `notes` optional, `approval_id` optional when multiple pendings.
- **Logging:** Structured `Log::info` on start, approve, reject with `action`, ids, `user_id`.
- **Policies:** `WorkflowInstancePolicy` for view/approve/reject tied to project visibility.

### Frontend

- `pages/admin/workflows.vue` — admin-only, `useApi`, UTable, i18n keys in `ar.json` / `en.json`.
- Optional link from admin shell navigation if a central nav file exists (only if already pattern for other admin pages).

### Non-goals (this slice)

- Full notification fan-out (hooks only via logs; no new notification classes required).
- Escalation / timeout automation jobs.
- Rich workflow diagram designer UI.

## Acceptance criteria

- Migrations are forward-only with `down()`; `php artisan migrate --pretend` succeeds.
- Feature tests: admin workflows index; forbidden for non-admin; start workflow; pending list role-filtered; approve transitions pending row.
- `composer run lint` + `composer run test`; frontend `npm run lint`, `npm run typecheck`, `npm run test` pass.

## Clarifications

### Session 2026-04-12

- **Definitions vs tables:** REST `workflows` maps to `workflow_configurations`; no new `workflow_definitions` physical table in v1.
- **Steps:** Logical steps are `approval_rules` ordered by `id` (optional `sort_order` column deferred unless needed for ordering bugs).
- **Start conflict:** If an instance with status `pending` or `in_progress` exists for the same project and configuration, return HTTP 422 with error contract; no duplicate instances.
- **Paid / task graph:** Task status transitions remain in `TaskService` for this slice; workflow instances focus on **project-level** rules where `entity_type = project` in `approval_rules`.
