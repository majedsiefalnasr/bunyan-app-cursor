# Performance Checklist — Inventory

- [x] List endpoints paginate (default 15)
- [x] Eager-load `product` (and `variant` when used) on inventory index
- [x] Indexes on `product_id`, low-stock filter columns
