# Security Checklist — API Foundation

- [ ] Public routes only expose intended operations; no debug endpoints in production.
- [ ] `auth:sanctum` enforced for protected JSON routes.
- [ ] Role middleware applied to privileged route groups.
- [ ] Rate limiting on login, password reset, and verification routes.
- [ ] Health endpoint does not leak secrets, stack traces, or internal hostnames beyond safe metadata.
