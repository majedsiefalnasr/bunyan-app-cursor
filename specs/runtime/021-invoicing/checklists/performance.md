# Performance Checklist — Invoicing

- [x] Invoice index paginated; default per_page capped
- [x] Eager load `items`, `order`, `customer`, `supplierProfile` on show
- [x] PDF generation does not load unbounded relations
