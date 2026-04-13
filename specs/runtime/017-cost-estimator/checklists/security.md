# Security Checklist — Cost Estimator

- [x] RBAC middleware on all estimate and BOQ template routes
- [x] `EstimatePolicy` enforces `ProjectPolicy::view` and role rules for mutations
- [x] Admin-only mutations on `boq_templates`
- [x] Compare and export cannot leak cross-project ids
- [x] Input validation on all write endpoints (Form Requests)
- [x] No trusted client totals without server recalculation path
