# Security Checklist — Categories

- [ ] Admin mutations only via `role:admin` middleware + `CategoryPolicy`
- [ ] Form requests validate `parent_id`, slug uniqueness, string lengths
- [ ] No slug or ID injection in raw queries (Eloquent only)
- [ ] Tree move rejects cycles and invalid `parent_id`
