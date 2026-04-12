# Clarify Report — Categories

> **Phase:** 02_CATALOG_AND_INVENTORY > **Generated:** 2026-04-12T14:08:00Z

## Clarifications captured

Session 2026-04-12 (see `spec.md` → Clarifications):

- Authenticated-only listing; optional `include_inactive=1` for admin.
- Soft delete; delete forbidden when children exist.

## Ambiguities resolved

| Topic               | Resolution                                    |
| ------------------- | --------------------------------------------- |
| Guest access        | 401 on category routes without Sanctum token  |
| Inactive visibility | Filtered for non-admin list                   |
| Hard vs soft delete | Soft delete; guard delete when children exist |

## Risk level

LOW
