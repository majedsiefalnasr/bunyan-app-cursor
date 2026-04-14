# Tasks Report — Admin Pages

> **Phase:** 07_FRONTEND_APPLICATION > **Generated:** 2026-04-14T14:28:38Z

## Task Summary

| Metric         | Value |
| -------------- | ----- |
| Total Tasks    | 18    |
| Parallelizable | 4     |
| Sequential     | 14    |
| HIGH Risk      | 2     |
| MEDIUM Risk    | 7     |
| LOW Risk       | 9     |

## Risk-Ranked Task View

### 🔴 HIGH Risk Tasks

| ID   | Description                                                      | Risk Factor                                      |
| ---- | ---------------------------------------------------------------- | ------------------------------------------------ |
| T002 | Refactor `frontend/layouts/admin.vue` to Nuxt UI dashboard shell | Wide UI impact across all admin pages            |
| T018 | Run lint/typecheck/tests and stabilize                           | Ensures refactor didn’t break global UX or build |

### 🟡 MEDIUM Risk Tasks

| ID   | Description                 | Risk Factor                                              |
| ---- | --------------------------- | -------------------------------------------------------- |
| T005 | Add `/admin/users/[id].vue` | Requires correct API contract discovery + error handling |
| T006 | Add `/admin/roles.vue`      | Depends on role/permission contract                      |
| T007 | Permission matrix component | Backend mutation support may be missing                  |
| T011 | `/admin/settings.vue`       | API may not exist; must degrade gracefully               |
| T012 | `/admin/notifications.vue`  | API may not exist; must degrade gracefully               |
| T014 | Admin composables layer     | Touches multiple pages; needs consistency                |
| T017 | Playwright admin e2e        | Test environment/fixtures risk                           |

### 🟢 LOW Risk Tasks

| ID   | Description                                 | Risk Factor      |
| ---- | ------------------------------------------- | ---------------- |
| T001 | Audit existing admin pages                  | Read-only        |
| T003 | Standardize `definePageMeta` on admin pages | Straightforward  |
| T004 | Add `/admin` index page                     | Routing only     |
| T008 | Users list UX improvements                  | Localized change |
| T009 | Activity log styling alignment              | Styling only     |
| T010 | Reports/analytics styling alignment         | Styling only     |
| T013 | Admin navigation entries                    | Layout-only      |
| T015 | Add translation keys                        | i18n wiring      |
| T016 | Vitest unit tests                           | Localized        |

## External Dependencies

| Task ID | Package/Library | Version | Purpose                               |
| ------- | --------------- | ------- | ------------------------------------- |
| —       | —               | —       | No new packages planned in this stage |

## High-Downstream-Impact Tasks

| Task ID | Description           | Downstream Impact                            |
| ------- | --------------------- | -------------------------------------------- |
| T002    | Admin layout refactor | All admin pages; global admin UX consistency |
