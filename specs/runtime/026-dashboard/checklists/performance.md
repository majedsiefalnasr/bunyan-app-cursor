# Performance Checklist — Dashboard

- [ ] Aggregations use indexed columns (`orders.status`, `orders.customer_id`, `orders.supplier_id`).
- [ ] Activity list eager-loads `actor` relation.
- [ ] Response cache TTL bounded; cache key includes user id and role.
