# API Contracts — Workflow Engine

## Workflows (admin)

- `GET /api/v1/workflows` → paginated list of definitions.
- `POST /api/v1/workflows` → create (body: `name`, `name_ar`, `name_en`, `description`, `type`, `is_global`, `project_id`, JSON fields).
- `GET /api/v1/workflows/{id}` → single definition with `approval_rules`.

## Project workflow

- `POST /api/v1/projects/{project}/workflow/start` → `{ data: { instance, approvals } }`.

## Approvals

- `GET /api/v1/approvals/pending` → list pending rows for current user role.
- `PUT /api/v1/workflow-instances/{workflowInstance}/approve` body: `{ "notes": "..." , "approval_id": 1 }` (approval_id optional if single pending).
- `PUT .../reject` — same body contract.

All responses: Bunyan `{ success, data, message, errors }`.
