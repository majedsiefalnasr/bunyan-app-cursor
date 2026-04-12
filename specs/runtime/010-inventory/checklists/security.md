# Security Checklist — Inventory

- [x] No inventory mutation without `manageInventory` policy
- [x] Contractor cannot pass arbitrary `product_id` to escape supplier scope
- [x] Mass-assignment guarded on models (`$fillable`)
- [x] Throttle mutating routes where appropriate
