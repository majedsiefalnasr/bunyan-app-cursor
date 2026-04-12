# Data Model — Project Pages (frontend view-models)

## ProjectDetail

| Field      | Type   | Source            |
| ---------- | ------ | ----------------- |
| id         | number | `ProjectResource` |
| name       | string | `ProjectResource` |
| status     | string | `ProjectResource` |
| start_date | string | nullable          |
| end_date   | string | nullable          |

## TaskRow

| Field    | Type   | Notes             |
| -------- | ------ | ----------------- |
| id       | number |                   |
| status   | string | Kanban column key |
| title_ar | string | nullable          |
| name     | string | fallback label    |

## Team payloads

Reuse shapes from existing `ProjectTeamController` JSON (members + invitations_pending).

## EstimatesRow (client-only)

| Field      | Type   |
| ---------- | ------ | ---------------- |
| id         | string | uuid client-side |
| label      | string |
| quantity   | number |
| unit_price | number |
