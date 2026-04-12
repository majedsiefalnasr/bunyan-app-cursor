# Testing Guide — Team Management

> **Phase:** 03_PROJECT_MANAGEMENT > **Generated:** 2026-04-12T16:25:00Z

## Prerequisites

```bash
cd backend && composer install && php artisan migrate:fresh --seed
cd ../frontend && npm install
```

Ensure `SANCTUM_STATEFUL_DOMAINS` and `API_URL` / `NUXT_PUBLIC_API_BASE_URL` match your local frontend origin.

## Automated tests

```bash
cd backend && php artisan test --filter=ProjectTeamControllerTest
cd backend && php artisan test --filter=DatabaseSchemaTest
cd frontend && npm run test
cd frontend && npm run typecheck
```

## Manual scenario — list team

**Preconditions:** Customer user `customer@example.test` owns project `id=1` (seed or create via UI).

```bash
curl -sS -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json" \
  http://localhost:8000/api/v1/projects/1/team
```

Expect `200`, `success: true`, `data.members` non-empty with one `owner` row for the customer.

## Manual scenario — invite and accept

1. As customer, `POST /api/v1/projects/1/team` with JSON `{"email":"new-member@example.test","project_role":"viewer"}`.
2. Copy `data.accept_token` from the JSON body.
3. Register or use an existing user whose email is exactly `new-member@example.test`.
4. `POST /api/v1/invitations/<accept_token>/accept` with that user’s bearer token.

Expect `200` and a `project_member` with `project_role` `viewer`.

## Manual scenario — forbid owner removal

As the project customer, attempt:

```bash
curl -sS -X DELETE -H "Authorization: Bearer <TOKEN>" \
  http://localhost:8000/api/v1/projects/1/team/<CUSTOMER_USER_ID>
```

Expect `422` validation-style failure (cannot remove canonical owner).

## UI

1. Log in as customer, open `/projects/1` (or created id).
2. Confirm “فريق المشروع” card lists members.
3. Enter an email and role, submit invitation; confirm toast success and pending invitation row updates after reload.
