# Security Checklist — Inventory

- [ ] No inventory mutation without `manageInventory` policy
- [ ] Contractor cannot pass arbitrary `product_id` to escape supplier scope
- [ ] Mass-assignment guarded on models (`$fillable`)
- [ ] Throttle mutating routes where appropriate
