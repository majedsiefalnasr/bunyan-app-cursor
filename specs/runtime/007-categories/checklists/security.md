# Security Checklist — Categories

- [x] Admin mutations only via `role:admin` middleware + `CategoryPolicy`
- [x] Form requests validate `parent_id`, slug uniqueness, string lengths
- [x] No slug or ID injection in raw queries (Eloquent only)
- [x] Tree move rejects cycles and invalid `parent_id`
