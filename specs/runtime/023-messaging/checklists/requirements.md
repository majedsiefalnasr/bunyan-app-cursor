# Requirements Checklist — Messaging

- [x] Conversations and messages persisted with migrations and `down()` rollback.
- [x] Participant model prevents duplicate `(conversation_id, user_id)`.
- [x] Direct conversations enforce exactly two participants.
- [x] Project-scoped conversations validate project visibility for creator.
- [x] All messaging routes use `auth:sanctum` and policy checks (no IDOR).
- [x] Form Request validation on create conversation, send message, mark read.
- [x] API responses follow Bunyan success/error JSON contract.
- [x] Arabic-first copy for user-visible API messages where applicable.
- [x] Read receipts update only the authenticated participant row.
- [x] File uploads validated (size, MIME) and stored safely.
- [x] Broadcast event fires on new message; channel auth restricts to participants.
- [x] PHPUnit feature tests for happy and forbidden paths.
- [x] Nuxt pages use `useApi`, auth middleware, and i18n for labels.
