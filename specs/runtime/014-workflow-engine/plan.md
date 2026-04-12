# Technical Plan — Workflow Engine

## Architecture

- **Controllers:** `WorkflowDefinitionController`, `ProjectWorkflowController`, `WorkflowInstanceActionController`, `PendingApprovalController` under `App\Http\Controllers\Api\V1`.
- **Services:** `WorkflowDefinitionService`, `WorkflowEngineService`.
- **Repositories:** `WorkflowInstanceRepository`, `WorkflowApprovalRepository`; extend `WorkflowConfigurationRepository` with create/update helpers if needed.
- **Resources:** `WorkflowDefinitionResource`, `WorkflowInstanceResource`, `WorkflowApprovalResource`.
- **Policies:** `WorkflowInstancePolicy`.
- **Form Requests:** `StoreWorkflowDefinitionRequest`, `UpdateWorkflowDefinitionRequest`, `StartProjectWorkflowRequest`, `ResolveWorkflowApprovalRequest`.

## Migrations

1. `add_workflow_engine_fields_to_workflow_configurations_table` — `type` (string, nullable), `name_ar`, `name_en` (nullable strings), `is_active` (boolean, default true).
2. `create_workflow_instances_table` — FK `workflow_configuration_id`, morph `workflowable`, `status` string, indexes.
3. `create_workflow_approvals_table` — FK `workflow_instance_id`, FK `approval_rule_id` nullable, `approver_role`, `action`, `notes`, `acted_by`, `acted_at`.

## Routes (`routes/api.php`)

Inside `auth:sanctum`:

- `middleware role:admin` → `GET/POST /workflows`, `GET /workflows/{workflowConfiguration}`.
- `POST /projects/{project}/workflow/start` — middleware role including customer, contractor, supervising_architect, admin + policy.
- `GET /approvals/pending` — authenticated (all roles that may approve).
- `PUT /workflow-instances/{workflowInstance}/approve|reject` — authenticated + policy.

## Guardian composite (Step 3.1A)

- `architecture_checker`: PASS — respects service/repository/controller split.
- `api_designer`: PASS — versioned plural routes, standard error envelope.

## Logging

Use structured context keys per observability standards for start/approve/reject.

## Frontend

- `pages/admin/workflows.vue`, i18n keys `workflow.admin_*`.

## Testing

- `tests/Feature/Api/WorkflowEngineTest.php` — admin list, 403 for customer on definitions, start workflow, pending filter, approve.
