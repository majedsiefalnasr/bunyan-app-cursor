# Data Model — Workflow Engine

## workflow_configurations (extended)

| Column    | Type    | Notes        |
| --------- | ------- | ------------ | ------------------------- |
| type      | string  | null         | Maps to WorkflowType enum |
| name_ar   | string  | null         | Display                   |
| name_en   | string  | null         | Display                   |
| is_active | boolean | default true |

(Existing: `project_id`, `name`, `description`, `status_transitions`, `approval_requirements`, `is_global`.)

## workflow_instances

| Column                    | Type   | Notes                                           |
| ------------------------- | ------ | ----------------------------------------------- |
| workflow_configuration_id | FK     | required                                        |
| workflowable_type         | string | e.g. `App\Models\Project`                       |
| workflowable_id           | bigint |                                                 |
| status                    | string | pending \| in_progress \| completed \| rejected |

## workflow_approvals

| Column               | Type     | Notes                           |
| -------------------- | -------- | ------------------------------- | --- |
| workflow_instance_id | FK       | required                        |
| approval_rule_id     | FK       | null                            |     |
| approver_role        | string   | matches `UserRole` value string |
| action               | string   | pending \| approved \| rejected |
| notes                | text     | null                            |     |
| acted_by             | FK users | null                            |     |
| acted_at             | datetime | null                            |     |

## Relationships

- `WorkflowConfiguration` hasMany `WorkflowInstance`.
- `WorkflowInstance` morphTo `workflowable`; hasMany `WorkflowApproval`.
- `WorkflowApproval` belongsTo `WorkflowInstance`, optional `ApprovalRule`, `User` as actor.
