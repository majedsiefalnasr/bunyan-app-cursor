# Clarify Report — Suppliers

> Generated: 2026-04-12

## Clarifications Captured

- Supplier modeled as profile on **contractor** user (no new `UserRole` case).
- Public read routes unauthenticated with throttle; writes authenticated + RBAC.
- Rating columns persisted; business aggregation deferred.

## Risk Level

MEDIUM (RBAC + public exposure of supplier data — mitigated by verified-only default catalog).
