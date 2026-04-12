# Clarify Report — Workflow Engine

> **Phase:** 03_PROJECT_MANAGEMENT > **Generated:** 2026-04-12T15:50:00Z

## Clarifications Captured

All items recorded under `## Clarifications` in `spec.md` (Session 2026-04-12).

## Summary

- Physical `workflow_definitions` table deferred; API uses `workflow_configurations`.
- Step ordering uses `approval_rules` (by `id`); optional `sort_order` deferred.
- Duplicate active instance for same project returns 422.
- Instance scope in v1 focuses on `entity_type = project` approval rules.

## Risk After Clarify

MEDIUM — reduced from HIGH by explicit table mapping and scope boundary.
