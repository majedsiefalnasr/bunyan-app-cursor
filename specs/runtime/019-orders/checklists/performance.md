# Performance Checklist — Orders

> **Stage:** Orders  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Generated:** 2026-04-13T15:35:00Z

- [ ] Order index paginated; indexed filters on `customer_id`, `supplier_id`, `status`
- [ ] Eager load `items.product` (and `variant` when present) on show/index
- [ ] Inventory reservation uses row locks (`lockForUpdate`) per product line
