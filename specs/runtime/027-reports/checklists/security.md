# Security Checklist — STAGE_27

- [ ] Admin middleware on all analytics report routes
- [ ] Form Request validates enums (`type`, `format`) against allow-list
- [ ] No user-controlled column names in ORDER BY
- [ ] Export filenames sanitized; no path traversal
- [ ] Audit log excludes PII beyond user id
