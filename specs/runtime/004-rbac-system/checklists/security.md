# Security Checklist — RBAC System

## Middleware & Authorization

- [ ] `CheckRole` middleware validates against `users.role` enum (not user-supplied string)
- [ ] `CheckPermission` middleware uses `Gate::check()` (not raw DB query in middleware)
- [ ] Both middleware return 403 with structured error response (no information leak)
- [ ] Admin routes protected by `auth:sanctum` + `role:admin` (double layer)
- [ ] No admin-only data accessible without admin role middleware
- [ ] `Gate::before()` admin bypass only applies to `admin` role (not configurable externally)

## Token & Session Security

- [ ] All user tokens revoked on role change (`$user->tokens()->delete()`)
- [ ] Token revocation is atomic with role update (same transaction)
- [ ] No permission data in token payload (permissions resolved server-side per request)

## Input Validation

- [ ] `AssignRoleRequest` validates role against `UserRole` enum values only
- [ ] Role assignment endpoint does not accept permission modifications
- [ ] User ID parameter validated as existing user (404 on invalid)
- [ ] No mass-assignment on role fields (only explicit `role` column updated)

## Access Control

- [ ] Cannot remove last admin user from admin role (safety check in `RoleService`)
- [ ] Non-admin users cannot access `/api/v1/admin/*` endpoints
- [ ] Permission cache keyed per-user (no cross-user cache pollution)
- [ ] Cache invalidation on role change is synchronous (before response)

## Audit Trail

- [ ] All role changes logged with: actor, target user, old role, new role, timestamp
- [ ] Token revocation events logged
- [ ] Failed authorization attempts logged (middleware + Gate)

## Data Privacy

- [ ] `UserAdminResource` does not expose password hash or tokens
- [ ] Permissions array only returned for authenticated user's own profile
- [ ] Non-admin users cannot list other users or their permissions
