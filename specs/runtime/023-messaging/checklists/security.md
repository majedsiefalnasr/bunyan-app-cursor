# Security Checklist — Messaging

- [ ] No conversation or message access without participant membership.
- [ ] File uploads scanned for allowed MIME and max size; stored outside web root with signed URLs if exposed.
- [ ] Rate limits on `POST` message and create conversation endpoints.
- [ ] Sanctum token required on all messaging routes.
- [ ] Channel authorization denies non-participants for `conversation.{id}`.
