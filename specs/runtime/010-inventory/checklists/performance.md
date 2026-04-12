# Performance Checklist — Inventory

- [ ] List endpoints paginate (default 15)
- [ ] Eager-load `product` (and `variant` when used) on inventory index
- [ ] Indexes on `product_id`, low-stock filter columns
