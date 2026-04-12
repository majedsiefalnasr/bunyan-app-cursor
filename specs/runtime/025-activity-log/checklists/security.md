# Security Checklist — Activity Log

- [ ] Admin activity index requires `role:admin` middleware.
- [ ] Subject timeline authorizes `view` on resolved model before returning rows.
- [ ] Query filters use validated allowlists (no raw `subject_type` class injection).
- [ ] Logged JSON excludes passwords, tokens, and remember tokens.
- [ ] Rate limiting considered for heavy list endpoints (reuse global throttle where applicable).
