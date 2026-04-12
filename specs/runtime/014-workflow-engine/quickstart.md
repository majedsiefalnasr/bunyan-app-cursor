# Quickstart — Workflow Engine

## Backend

```bash
cd backend && composer install && php artisan migrate
```

## Seed a minimal workflow (manual / factory)

1. Create `WorkflowConfiguration` with `is_global=true`, `type=project`.
2. Add `ApprovalRule` with `entity_type=project`, `status_from`/`status_to` placeholders, `approver_role=supervising_architect`.
3. `POST /api/v1/projects/{id}/workflow/start` as project stakeholder.
4. `GET /api/v1/approvals/pending` as supervising architect user.
5. `PUT /api/v1/workflow-instances/{id}/approve` with optional `notes`.

## Frontend

```bash
cd frontend && npm install && npm run dev
```

Navigate to `/admin/workflows` as admin.
