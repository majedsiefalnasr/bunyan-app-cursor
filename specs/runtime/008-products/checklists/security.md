# Security Checklist — Products

- [ ] Admin-only mutation routes return 403 for non-admin roles
- [ ] Form Requests reject invalid `category_id`, `supplier_id`, and price/stock inputs
- [ ] Path fields on `product_media` validated for length and allowed characters (no directory traversal patterns)
- [ ] Rate limits inherited from existing `throttle` groups on `api.php`
