# Clarify Report — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Generated:** 2026-04-11T00:00:00Z

## Clarification Summary

| Metric                | Value                                |
| --------------------- | ------------------------------------ |
| Questions Asked       | 4                                    |
| Questions Resolved    | 4                                    |
| Spec Sections Updated | Clarifications section added to spec |

## Resolved Clarifications

| #   | Topic                               | Resolution                                                                                 | Impact                                                             |
| --- | ----------------------------------- | ------------------------------------------------------------------------------------------ | ------------------------------------------------------------------ |
| 1   | `useAuth` stub vs real API          | Uses Pinia `auth` store with mock data — no API calls in this stage                        | Downstream auth stage handles real API; composable contract is set |
| 2   | Role-based nav enforcement location | Both client-side nav filtering AND route middleware stubs (full enforcement in auth stage) | Ensures nav items filtered immediately; full RBAC deferred cleanly |
| 3   | i18n URL strategy                   | `prefix_except_default` — Arabic at `/` (no prefix), English at `/en`                      | Affects `nuxt.config.ts` i18n config and `LanguageSwitcher` links  |
| 4   | `useApi` 401 auto-logout            | Yes — `useApi` catches 401 → `useAuth().logout()` → redirect to `/login`                   | Foundational security behavior for all downstream API calls        |

## Additional Ambiguity Scan Results

**Scanned sections:** Scope, User Stories, Technical Requirements, Non-Functional Requirements.

No new ambiguities detected. All edge cases handled:

- **Nuxt app scaffold existence** — confirmed as upstream dependency (STAGE_01)
- **Playwright setup** — included in this stage's test scope (E2E test file created in stage)
- **Geist font source** — preloaded via `nuxt.config.ts` from Google Fonts or local; implementation detail deferred to plan step
- **Pinia store hydration on SSR** — `useAuth` composable uses `useCookie` for token persistence if SSR is enabled; flagged for plan step
- **`useColorMode` vs Nuxt UI AppConfig** — Nuxt UI v3 uses its own color mode system; plan step to resolve exact API

## Remaining Ambiguities

None — all clarifications resolved.

## Checklists Generated

| Checklist     | Path                        | Items |
| ------------- | --------------------------- | ----- |
| Requirements  | checklists/requirements.md  | 45    |
| Security      | checklists/security.md      | 20    |
| Accessibility | checklists/accessibility.md | 28    |
