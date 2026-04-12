# Quickstart — Team Management

1. Run migrations: `cd backend && php artisan migrate`
2. Seed or create a project as customer; confirm `GET /api/v1/projects/{id}/team` returns owner member.
3. As contractor on same project, `POST /api/v1/projects/{id}/team` with `{ "email": "...", "project_role": "engineer" }`.
4. Register/login as invitee email, `POST /api/v1/invitations/{token}/accept`.
5. Open Nuxt `/projects/{id}` and verify team card lists members.
