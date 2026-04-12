# Performance Checklist — Messaging

- [ ] Conversation list eager-loads last message or uses subquery for sort key to avoid N+1.
- [ ] Message index paginates (default 30) with stable ordering by `id` / `created_at`.
- [ ] Indexes on `conversation_participants(user_id)`, `messages(conversation_id, created_at)`.
