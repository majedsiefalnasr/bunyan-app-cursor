# Performance Checklist — Invoicing

- [ ] Invoice index paginated; default per_page capped
- [ ] Eager load `items`, `order`, `customer`, `supplierProfile` on show
- [ ] PDF generation does not load unbounded relations
