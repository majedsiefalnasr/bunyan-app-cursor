# Analytics — Research Notes

## Existing Sources of Truth (expected)

- Users/roles: backend user tables + Sanctum auth
- Commerce: orders, payments/transactions, carts
- Projects: projects, phases, tasks statuses
- Suppliers: supplier verification/status + response-time signals

## Key Decisions (locked in Clarify)

- Access: **Admin + Supervising Architect**
- Storage: **raw events + aggregated rollups**
- Freshness: **~5 minutes**

## Performance Approach

- Prefer scheduled rollups (jobs) for metrics that require heavy joins.
- Read endpoints return pre-aggregated values and are cached.
