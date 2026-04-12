# API Contract — Projects

## Success envelope

Responses follow Bunyan `{ success, data, message, errors }`.

## `ProjectResource` (representative)

- `id`, `name`, `name_ar`, `name_en`, `description`, `status` (string value), `budget`, `budget_estimated`, `budget_actual`
- `location`, `city`, `district`, `location_lat`, `location_lng`, `project_type`
- `start_date`, `end_date` (ISO8601)
- Nested relations when loaded: `customer`, `contractor`, `supervising_architect`
- `phases_count`, `tasks_count` when counted

## `PUT /api/v1/projects/{project}/status`

Body:

```json
{ "status": "planning" }
```

422 when transition invalid.

## `GET /api/v1/projects/{project}/timeline`

Data:

```json
{
  "project": {
    "id": 1,
    "name": "...",
    "start_date": "...",
    "end_date": "..."
  },
  "phases": [
    {
      "id": 1,
      "name": "...",
      "start_date": "...",
      "end_date": "...",
      "completion_percentage": 40,
      "sort_order": 0
    }
  ]
}
```
