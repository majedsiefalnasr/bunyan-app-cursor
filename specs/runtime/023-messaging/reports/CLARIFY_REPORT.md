# Clarify Report — Messaging

> **Generated:** 2026-04-12T14:08:00Z

## Clarifications Captured

From `spec.md` § Clarifications (Session 2026-04-12):

- Participant-based authorization for all roles with Sanctum.
- `PUT /api/v1/conversations/{id}/read` for read receipts.
- Attachment size and MIME constraints defined.
- Broadcasting scaffold without Reverb in CI; Echo deferred to deployment config.

## Ambiguity Resolution

| Topic            | Resolution                                    |
| ---------------- | --------------------------------------------- |
| Coarse RBAC      | Not used beyond `auth:sanctum`; policy-driven |
| Read verb        | `PUT` per stage contract                      |
| Real-time client | Env-specific; backend event required          |

## Checklists Generated

- `checklists/security.md`
- `checklists/performance.md`
- `checklists/accessibility.md`
