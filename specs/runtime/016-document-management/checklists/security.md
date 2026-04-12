# Security Checklist — Document Management

- [ ] All document routes require `auth:sanctum` and role middleware
- [ ] `DocumentPolicy` prevents cross-project access on every ID-bound route
- [ ] File MIME and extension validated; max size enforced server-side
- [ ] Download responses do not leak paths to unauthorized users
- [ ] Rate limiting on upload endpoints
