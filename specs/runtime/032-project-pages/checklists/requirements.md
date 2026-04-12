# Requirements Checklist — Project Pages

## Specification

- [x] Scope references existing v1 APIs only
- [x] Non-goals explicit (BOQ backend, Gantt, DnD)
- [x] Acceptance criteria testable

## Security & RBAC

- [ ] All new pages use `middleware: 'auth'` (or equivalent meta)
- [ ] Destructive actions remain gated by existing backend policies
- [ ] No secrets or tokens in client storage beyond session

## Data & API

- [ ] `useApi` for all HTTP calls
- [ ] Handles paginated and array task list payloads

## UX / i18n

- [ ] Arabic + English strings via i18n keys
- [ ] Shadow-as-border cards per `DESIGN.md`

## Quality

- [ ] Vitest for new composables
- [ ] Playwright smoke for `/projects/create` testids
