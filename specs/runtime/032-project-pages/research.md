# Research — Project Pages

## Nuxt 3 nested routes

Parent route component renders matched child via `<NuxtPage />`. Sibling file `[id].vue` wraps `[id]/index.vue`, `[id]/tasks.vue`, etc.

## Nuxt UI 2.17

- Stepped flows implemented with discrete `UCard` sections and primary/secondary `UButton`s.
- Horizontal navigation pattern: `UButton` group with `variant="soft"` and active state from `useRoute().path`.

## Existing APIs (Laravel `api.php`)

- `GET/POST /v1/projects`, `GET /v1/projects/{project}`, `GET /v1/projects/{project}/timeline`
- `GET/POST /v1/projects/{project}/team`
- `GET /v1/projects/{project}/tasks`
- `GET/POST /v1/projects/{project}/documents` (indexed)
- `POST /v1/projects/{project}/workflow/start`

## Risks

- `start_date` rule: omit field on create unless product requires scheduling later.
