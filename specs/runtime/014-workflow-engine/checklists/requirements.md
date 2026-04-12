# Requirements Checklist — Workflow Engine (STAGE_14)

- [ ] Admin `GET/POST /workflows` and `GET /workflows/{id}` with RBAC and Form Requests
- [ ] Forward-only migrations: configuration columns + `workflow_instances` + `workflow_approvals`
- [ ] `WorkflowEngineService` for start, pending, approve, reject; repositories only for persistence
- [ ] Policies on workflow instances; no IDOR on project-bound instances
- [ ] `POST /projects/{project}/workflow/start` authorized by project access
- [ ] Feature tests for RBAC and happy paths
- [ ] Admin Nuxt `workflows` page with RTL/i18n
- [ ] API error contract on all new endpoints
