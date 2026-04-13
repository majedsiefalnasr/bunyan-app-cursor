# Performance Checklist — Quotations

> **Stage:** Quotations  
> **Phase:** 04_COMMERCIAL_LAYER  
> **Generated:** 2026-04-13T09:47:15Z

## API Performance

- [ ] RFQ list is paginated and filterable by status
- [ ] RFQ quotations list is paginated
- [ ] All list/detail endpoints eager-load required relationships (avoid N+1)
- [ ] Comparison endpoint is bounded (caps) and uses constant-query aggregation (no per-row queries)
- [ ] Foreign keys indexed (`rfq_id`, `supplier_id`, `rfq_item_id`, `quotation_id`)
- [ ] Supplier eligibility uses `rfq_targets` snapshot (no table scan)

## Frontend Performance

- [ ] Heavy tables (comparison) virtualized if necessary
- [ ] Server responses are normalized to avoid overfetching
- [ ] Debounce filters/search in listing UIs
