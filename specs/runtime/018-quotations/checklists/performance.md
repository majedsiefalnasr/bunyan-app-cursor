# Performance Checklist — Quotations

> **Stage:** Quotations  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Generated:** 2026-04-13T09:47:15Z

## API Performance

- [ ] RFQ list is paginated and filterable by status
- [ ] All list/detail endpoints eager-load required relationships (avoid N+1)
- [ ] Comparison endpoint uses efficient aggregation, not per-row queries
- [ ] Foreign keys indexed (`rfq_id`, `supplier_id`, `rfq_item_id`, `quotation_id`)

## Frontend Performance

- [ ] Heavy tables (comparison) virtualized if necessary
- [ ] Server responses are normalized to avoid overfetching
- [ ] Debounce filters/search in listing UIs
