# Quickstart — RBAC System

## Prerequisites

- STAGE_03_AUTHENTICATION completed (Sanctum auth working)
- STAGE_02_DATABASE_SCHEMA completed (roles, permissions tables migrated and seeded)
- Redis available for permission caching

## Development Order

1. Backend middleware + Gates → Backend service/repository → Backend admin controller → Route restructuring → Seeders → Backend tests
2. Frontend composable + store update → Navigation update → Admin page → Page meta wiring → Frontend tests

## Key Commands

```bash
# Backend
cd backend
php artisan migrate:fresh --seed   # Reset DB with updated seeders
php artisan test --filter=RoleService
php artisan test --filter=RoleEndpoint
php artisan test --filter=CheckRole
php artisan test --filter=CheckPermission

# Frontend
cd frontend
npx vitest run composables/usePermission
npx vitest run middleware/role
```

## Validation

```bash
# Full CI validation
cd backend && composer run lint && composer run test
cd frontend && npm run lint && npm run typecheck && npm run test
```
