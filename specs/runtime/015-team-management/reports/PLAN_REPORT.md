# Plan Report — Team Management

> **Phase:** 03_PROJECT_MANAGEMENT > **Generated:** 2026-04-12T15:45:00Z

## Plan Summary

| Metric         | Value                     |
| -------------- | ------------------------- |
| New Tables     | 2                         |
| New Endpoints  | 5                         |
| New Services   | 1                         |
| New Pages      | 0 (extend project detail) |
| New Components | 0                         |

## Architecture Decisions

- Team mutations restricted to customer owner, contractor, supervising architect, and admin via `ProjectPolicy::manageTeam`.
- Invitation tokens are hashed at rest; plaintext returned only on invitation creation in API payload.
- Owner membership is backfilled for legacy projects and created automatically for new projects in `ProjectService::create`.

## Guardian Verdicts

| Guardian              | Verdict | Notes                          |
| --------------------- | ------- | ------------------------------ |
| Architecture Guardian | PASS    | Service/repository layering    |
| API Designer          | PASS    | `/api/v1` + Bunyan error shape |

## Risk Assessment

| Risk Level | Count | Details              |
| ---------- | ----- | -------------------- |
| HIGH       | 0     |                      |
| MEDIUM     | 0     |                      |
| LOW        | 1     | Backfill idempotency |
