# Project Pages — Performance Checklist

- [x] Task list capped at `per_page=100` for board view
- [x] Project shell fetches `GET /v1/projects/{id}` once in parent; children avoid duplicate project fetch where inject is used
