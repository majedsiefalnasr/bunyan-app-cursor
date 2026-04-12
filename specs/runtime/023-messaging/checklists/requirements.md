# Requirements Checklist — Messaging

- [ ] Conversations and messages persisted with migrations and `down()` rollback.
- [ ] Participant model prevents duplicate `(conversation_id, user_id)`.
- [ ] Direct conversations enforce exactly two participants.
- [ ] Project-scoped conversations validate project visibility for creator.
- [ ] All messaging routes use `auth:sanctum` and policy checks (no IDOR).
- [ ] Form Request validation on create conversation, send message, mark read.
- [ ] API responses follow Bunyan success/error JSON contract.
- [ ] Arabic-first copy for user-visible API messages where applicable.
- [ ] Read receipts update only the authenticated participant row.
- [ ] File uploads validated (size, MIME) and stored safely.
- [ ] Broadcast event fires on new message; channel auth restricts to participants.
- [ ] PHPUnit feature tests for happy and forbidden paths.
- [ ] Nuxt pages use `useApi`, auth middleware, and i18n for labels.
