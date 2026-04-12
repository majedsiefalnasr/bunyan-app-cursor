# Testing Guide — Workflow Engine

> **Phase:** 03_PROJECT_MANAGEMENT > **Generated:** 2026-04-12T20:15:00Z

## Prerequisites

```bash
cd backend && composer install && php artisan migrate:fresh --seed
cd ../frontend && npm install
```

## Automated tests

```bash
cd backend && php artisan test --filter=WorkflowEngineTest
cd backend && php artisan test --filter=DatabaseSchemaTest
cd ../frontend && npm run lint && npm run typecheck && npm run test
```

## Manual scenario A — Admin definitions list

1. Log in as **admin** (seeded admin user from `DatabaseSeeder`).
2. Open `http://localhost:3000/admin/workflows` (or your Nuxt dev URL).
3. Expect a table listing workflow configurations (may be empty before seeding definitions).

## Manual scenario B — Start workflow and approve

1. Ensure a **global** `workflow_configurations` row exists with `is_global=true` and an `approval_rules` row with `entity_type=project` and `approver_role=supervising_architect`.
2. As **customer**, obtain a Sanctum token (`POST /api/v1/auth/login`).
3. `POST /api/v1/projects/{projectId}/workflow/start` with `Authorization: Bearer {token}` — expect **201** and `data.status` = `in_progress` when rules exist.
4. As **supervising architect** for that project, `GET /api/v1/approvals/pending` — expect pending row with matching `id`.
5. `PUT /api/v1/workflow-instances/{instanceId}/approve` with JSON:

```json
{
    "approval_id": <pending approval id>,
    "notes": "Approved in QA"
}
```

6. Expect **200** and `data.status` = `completed` when all project rules are satisfied.

## Manual scenario C — Duplicate start (422)

Repeat step B.3 twice without completing the workflow — second call must return **422** with Arabic validation message under `errors.workflow`.

## Concrete sample values

- **Project ID:** any project where the acting user passes `ProjectPolicy::view`.
- **Approval ID:** returned as `data.approvals[0].id` from the start response.
- **Instance ID:** returned as `data.id` from the start response.
