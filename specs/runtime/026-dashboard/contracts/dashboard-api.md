# API Contract — Dashboard (`/api/v1`)

## GET `/dashboard`

**Auth:** Bearer Sanctum  
**Roles:** customer, contractor, supervising_architect, field_engineer, admin

**Response `data` (illustrative):**

```json
{
  "role": "customer",
  "kpis": {
    "projects": 2,
    "orders": 5,
    "tasks_assigned": 0,
    "reports": 0,
    "users": null,
    "revenue_sar": null
  }
}
```

Admin `kpis` include platform `users`, `projects`, `orders`, `revenue_sar`. Non-relevant keys may be omitted or `null`.

## GET `/dashboard/metrics`

Same auth. Returns `{ "metrics": { ... } }` flat map mirroring KPI numbers.

## GET `/dashboard/recent-activity?per_page=15`

**Query:** `per_page` optional integer 5–50.

**Response:** Paginated shape compatible with `ActivityLogResource` collection (`data` array of log rows).
