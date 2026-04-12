# Requirements Checklist — Workflow Engine (STAGE_14)

- [x] Admin `GET/POST /workflows` and `GET /workflows/{id}` with RBAC and Form Requests
- [x] Forward-only migrations: configuration columns + `workflow_instances` + `workflow_approvals`
- [x] `WorkflowEngineService` for start, pending, approve, reject; repositories only for persistence
- [x] Policies on workflow instances; no IDOR on project-bound instances
- [x] `POST /projects/{project}/workflow/start` authorized by project access
- [x] Feature tests for RBAC and happy paths
- [x] Admin Nuxt `workflows` page with RTL/i18n
- [x] API error contract on all new endpoints
