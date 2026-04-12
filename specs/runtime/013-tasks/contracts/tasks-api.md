# Task API Contract (v1)

Base: `/api/v1`

## Project tasks

- `GET /projects/{project}/tasks` — paginated list; query: `phase_id`, `status`, `priority`, `assigned_to`, `per_page`
- `POST /projects/{project}/tasks` — body: `phase_id` (required), `title_ar` (required), `title_en`, `description`, `priority`, `budget`, `assigned_to`, `due_date`, `estimated_hours`, `start_date`, `end_date`

## Task workspace

- `GET /tasks/{task}` — detail + comments
- `PUT /tasks/{task}` — partial update fields
- `PUT /tasks/{task}/assign` — `{ assigned_to: int|null }`
- `PUT /tasks/{task}/status` — `{ status: string }` validated transition
- `POST /tasks/{task}/comments` — `{ body: string }`

Responses: `TaskResource` extended with `project_id`, `title_ar`, `title_en`, `priority`, comments collection.
