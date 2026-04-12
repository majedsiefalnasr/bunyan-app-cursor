# Tasks — API Foundation

- [x] T001 [P] [US5] Add baseline OpenAPI document at `backend/docs/openapi/openapi.yaml`
- [x] T002 [P] [US4] Create `backend/app/Http/Controllers/Api/V1/HealthController.php` (include `correlation_id` in `data` when request attribute is set)
- [x] T003 [US4] Register `GET /api/v1/health` in `backend/routes/api.php` (public, inside `v1` prefix)
- [x] T004 [US3] Publish and tune `backend/config/cors.php` for `api/*` and Sanctum
- [x] T005 [US4] Add feature test `backend/tests/Feature/Api/V1/HealthEndpointTest.php`
