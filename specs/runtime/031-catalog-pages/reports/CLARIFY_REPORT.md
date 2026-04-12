# Clarify Report — Catalog Pages

> **Generated:** 2026-04-12T20:10:00Z

## Clarifications Captured

| #   | Topic            | Resolution                                                |
| --- | ---------------- | --------------------------------------------------------- |
| 1   | Category binding | API + Nuxt use slug for `{category}`                      |
| 2   | Product binding  | id or SKU in `{product}` segment                          |
| 3   | Search           | Reuse products index API with `search` query              |
| 4   | Suppliers        | Numeric id only in this slice                             |
| 5   | Auth             | Catalog pages auth; supplier directory unchanged (public) |

## Risk Level

**LOW** — Clarifications lock scope without new RBAC surfaces.

## Checklists Generated

- `checklists/security.md`
- `checklists/performance.md`
