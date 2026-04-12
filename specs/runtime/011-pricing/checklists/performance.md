# Performance Checklist — Pricing

- [ ] Tier lists loaded in one query per product (eager scope by `product_id`)
- [ ] Avoid N+1 on product detail when embedding tiers (single relation load)
- [ ] Validate tier count limits in Form Request (e.g. max 50 rows per request)
