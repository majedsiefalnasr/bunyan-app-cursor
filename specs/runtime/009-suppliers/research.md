# Suppliers — Research Notes

- Laravel implicit route model binding for `{supplierProfile}` with `SupplierProfile` model.
- Existing API uses `/api` base in Nuxt `apiBaseUrl`; client paths are `/v1/...`.
- RBAC uses `CheckRole` middleware alias `role` with comma-separated role values.
- Product listing for suppliers reuses `ProductResource`; `quantity` field aligned with `quantity_in_stock` for API consistency on this branch.
