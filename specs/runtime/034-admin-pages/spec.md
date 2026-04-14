# Spec — Admin Pages (لوحات الإدارة)

> **Phase:** 07_FRONTEND_APPLICATION  
> **Stage:** STAGE_34_ADMIN_PAGES  
> **Branch:** `spec/034-admin-pages`  
> **Last Updated (UTC):** 2026-04-14T13:58:31Z

## Problem Statement (ملخص المشكلة)

We need a full **Admin Panel** experience in the Nuxt 3 frontend that enables platform administrators to manage users, roles/permissions, categories, suppliers, settings, notifications templates, activity logs, reports, and analytics — with Arabic-first UX (RTL) and Nuxt UI components.

## Goals (الأهداف)

- Provide a consistent admin shell/layout for all `/admin/**` pages.
- Implement admin CRUD/management pages defined in stage scope.
- Enforce **frontend route guarding** for admin-only routes (UX layer), while assuming **server-side RBAC remains the source of truth**.
- Keep all API interactions via existing Laravel REST API client composables (no direct DB access).

## Non-Goals (خارج النطاق)

- Building backend endpoints or changing backend authorization rules in this stage (unless explicitly required by missing APIs discovered later).
- Implementing a full design overhaul outside `DESIGN.md` + Nuxt UI conventions.

## In Scope (ضمن النطاق)

### Pages & Routes

- `/admin` — Admin dashboard with KPI overview
- `/admin/users` — user list, filters, actions
- `/admin/users/:id` — user detail + activity snippet
- `/admin/roles` — role + permission management (matrix UI)
- `/admin/categories` — category tree management UI
- `/admin/suppliers` — supplier verification workflow UI
- `/admin/settings` — platform settings form
- `/admin/notifications` — notification templates editor (email/SMS)
- `/admin/activity-log` — platform activity log table/feed with pagination
- `/admin/reports` — report generation/download UI
- `/admin/analytics` — analytics dashboard (charts)

### UI Components (Nuxt UI)

- Layout: `UDashboardLayout`, `UDashboardSidebar`
- Tables: `UTable` + search/filter bar
- Forms: `UForm`, `UFormField`, `UInput`, `USelect`, `USwitch`
- Status: `UBadge`
- Modals/confirmations: `UModal` / `UConfirm` (or equivalent Nuxt UI pattern)

## User Stories (قصص المستخدم)

- US1: As an Admin, I can view a dashboard overview at `/admin`.
- US2: As an Admin, I can list users, filter/sort them, and perform actions (activate/suspend).
- US3: As an Admin, I can assign roles and permissions.
- US4: As an Admin, I can manage category hierarchy.
- US5: As an Admin, I can review and verify suppliers.
- US6: As an Admin, I can edit platform settings and see them persist.
- US7: As an Admin, I can manage notification templates (email/SMS).
- US8: As an Admin, I can view an activity log with pagination.
- US9: As an Admin, I can generate/download reports.
- US10: As an Admin, I can view analytics charts.
- US11: As a non-admin user, I am prevented from accessing `/admin/**` routes (redirected away).

## Acceptance Criteria (معايير القبول)

- All `/admin/**` pages use a consistent admin layout and navigation.
- Admin-only routing is enforced via Nuxt route middleware (UX gate).
- Each page renders correctly in **RTL** (Arabic-first), with content text ready for i18n keys.
- Lists are paginated and support basic filtering/sorting in UI where applicable.
- Forms validate client-side where feasible and show server validation errors using the standard API error contract.
- No hardcoded secrets and no direct backend coupling beyond REST calls.

## Dependencies

- Nuxt UI module installed and configured (upstream stage indicates Nuxt shell exists).
- Existing backend endpoints for users/roles/permissions/categories/suppliers/settings/activity log/reports/analytics (to be confirmed during Clarify/Plan).

## Open Questions

- Which exact API endpoints already exist for each admin page (and their request/response contracts)?
- What is the canonical roles/permissions model exposed to the frontend (role keys, permission keys)?
- What admin navigation information architecture is preferred (grouping + labels in Arabic/English)?
