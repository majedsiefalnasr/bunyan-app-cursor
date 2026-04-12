# Plan Report — Cost Estimator

> **Phase:** 04_COMMERCIAL_LAYER > **Generated:** 2026-04-12T12:35:00Z

## Plan Summary

| Metric         | Value                 |
| -------------- | --------------------- |
| New Tables     | 3                     |
| New Endpoints  | 15+                   |
| New Services   | 1 (`EstimateService`) |
| New Pages      | 2                     |
| New Components | 0 (inline pages)      |

## Architecture Decisions

- Reuse `ProjectPolicy::view` for tenancy; dedicated `EstimatePolicy` for estimate-level mutations and approval.
- Field engineer read-only via route group split (no write routes in FE middleware alignment).
- CSV export instead of PDF to avoid new binary dependencies.

## Guardian Verdicts

| Guardian              | Verdict | Notes                                                  |
| --------------------- | ------- | ------------------------------------------------------ |
| Architecture Guardian | PASS    | Service/repository/controller split preserved          |
| API Designer          | PASS    | `/api/v1` nesting, plural resources, standard envelope |

## Risk Assessment

| Risk Level | Count | Details                                              |
| ---------- | ----- | ---------------------------------------------------- |
| HIGH       | 0     |                                                      |
| MEDIUM     | 1     | Financial totals — server-side recalculation + tests |
| LOW        | 2     | Admin templates JSON size; CSV edge cases            |
