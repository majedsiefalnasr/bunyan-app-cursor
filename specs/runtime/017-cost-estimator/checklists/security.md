# Security Checklist — Cost Estimator

- [ ] RBAC middleware on all estimate and BOQ template routes
- [ ] `EstimatePolicy` enforces `ProjectPolicy::view` and role rules for mutations
- [ ] Admin-only mutations on `boq_templates`
- [ ] Compare and export cannot leak cross-project ids
- [ ] Input validation on all write endpoints (Form Requests)
- [ ] No trusted client totals without server recalculation path
