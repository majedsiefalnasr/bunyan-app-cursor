# Research — Activity Log

## Laravel morphMany / polymorphic indexes

Use `morphs('subject')` helper for `subject_type` + `subject_id` plus composite index. Activity rows are append-only; use `created_at` ordering desc default.

## Differences vs `LogApiActivity` middleware

Middleware logs HTTP metadata to structured log channel; this stage persists **domain events** for user-visible audit timelines. Both may coexist.

## Nuxt admin patterns

Mirror `frontend/pages/admin/users.vue`: `definePageMeta` with `middleware: ['auth','role']`, `roles: ['admin']`, `useApi` + Nuxt UI table components.
