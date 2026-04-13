# Security Checklist — Dashboard

- [ ] All dashboard routes require Sanctum authentication.
- [ ] Role middleware matches RBAC matrix in spec.
- [ ] Admin-only global activity cannot be fetched by non-admin roles.
- [ ] No sensitive fields in activity JSON beyond existing ActivityLog policy.
