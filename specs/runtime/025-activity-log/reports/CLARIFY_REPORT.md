# Clarify Report — Activity Log

> **Generated:** 2026-04-12T12:53:53Z

## Clarifications Captured

Documented under `spec.md` → **## Clarifications → Session 2026-04-12**:

- Admin index nested under `/api/v1/admin/activity-log` for consistency.
- Subject route uses allowlisted `{entity}` values; `Project` wired for automatic logging first.
- Retention default `365` days with Artisan prune command.

## Ambiguity Resolution

RBAC matrix aligned with existing `ProjectPolicy::view`. Payload redaction rules captured in spec non-goals / privacy notes.

## Follow-ups

None blocking planning.
