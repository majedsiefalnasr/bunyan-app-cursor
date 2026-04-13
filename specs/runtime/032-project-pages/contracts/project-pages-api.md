# API Contract — Project Pages (read-only reference)

Base: `/api/v1` with Sanctum session.

| Method | Path                                 | Notes                           |
| ------ | ------------------------------------ | ------------------------------- |
| GET    | `/projects`                          | List                            |
| POST   | `/projects`                          | Create (`CreateProjectRequest`) |
| GET    | `/projects/{project}`                | Show                            |
| GET    | `/projects/{project}/timeline`       | Phases summary                  |
| GET    | `/projects/{project}/team`           | Members + invitations           |
| POST   | `/projects/{project}/team`           | Invite                          |
| GET    | `/projects/{project}/tasks`          | Query `per_page`                |
| GET    | `/projects/{project}/documents`      | List                            |
| POST   | `/projects/{project}/workflow/start` | Start workflow                  |

Responses follow Bunyan `{ success, data, message, errors }` contract.
