# Clarify Report — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Generated:** 2026-04-11T00:00:00Z

## Clarification Summary

| Metric                | Value                                |
| --------------------- | ------------------------------------ |
| Questions Asked       | 5                                    |
| Questions Resolved    | 5                                    |
| Spec Sections Updated | 3 (US1, Technical Requirements, AV3) |

## Resolved Clarifications

| #   | Topic                          | Resolution                                                              | Impact                                         |
| --- | ------------------------------ | ----------------------------------------------------------------------- | ---------------------------------------------- |
| 1   | Registration role restriction  | Only `customer` role allowed on self-registration; admin assigns others | Removed `role` from RegisterRequest            |
| 2   | Email verification enforcement | Soft requirement — unverified users can log in, banner displayed        | No blocking middleware needed                  |
| 3   | password_reset_tokens table    | Verify during implementation; create migration if absent                | Conditional migration added to scope           |
| 4   | Token expiration policy        | 24-hour expiration, no sliding, full revocation on logout               | sanctum.php publish required                   |
| 5   | Frontend redirect paths        | Standardize on `/ar` prefix; defer `useLocalePath()` migration          | Update useApi.ts redirects to match middleware |

## Remaining Ambiguities

- None — all clarifications resolved.

## Checklists Generated

| Checklist     | Path                        | Items |
| ------------- | --------------------------- | ----- |
| Requirements  | checklists/requirements.md  | 50+   |
| Security      | checklists/security.md      | 30+   |
| Accessibility | checklists/accessibility.md | 20+   |
