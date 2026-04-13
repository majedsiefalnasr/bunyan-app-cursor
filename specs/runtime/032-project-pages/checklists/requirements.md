# Requirements Checklist — Project Pages

## Specification

- [x] Scope references existing v1 APIs only
- [x] Non-goals explicit (BOQ backend, Gantt, DnD)
- [x] Acceptance criteria testable

## Security & RBAC

- [x] All new pages use `middleware: 'auth'` (or equivalent meta)
- [x] Destructive actions remain gated by existing backend policies
- [x] No secrets or tokens in client storage beyond session

## Data & API

- [x] `useApi` for all HTTP calls
- [x] Handles paginated and array task list payloads

## UX / i18n

- [x] Arabic + English strings via i18n keys
- [x] Shadow-as-border cards per `DESIGN.md`

## Quality

- [x] Vitest for new composables
- [x] Playwright smoke for `/projects/create` testids
