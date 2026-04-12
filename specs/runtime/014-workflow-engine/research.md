# Research — Workflow Engine

## Existing code

- `WorkflowConfiguration`, `ApprovalRule`, `WorkflowConfigurationRepository`, `ApprovalRuleRepository`.
- `TaskService` hard-coded task transitions (unchanged in this stage).
- `ProjectPolicy::view` for stakeholder visibility.

## Laravel references

- Form Request `authorize()` + `rules()`.
- API Resources wrapping `sendSuccess` from `BaseController`.
- MorphMany / morphTo for polymorphic workflowable.

## Nuxt patterns

- Admin pages: `middleware: ['auth','role']`, `roles: ['admin']`, `useApi` + `apiFetch`.
