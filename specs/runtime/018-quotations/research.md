# Research — Quotations

## Known Existing Patterns in Codebase

- **Routes:** `backend/routes/api.php` uses `/api/v1/*`, `auth:sanctum`, and `role:*` middleware groups.
- **Layering:** Controllers inject Services; Services inject Repositories (see existing `*Service.php` + `*Repository.php` patterns).
- **Responses:** `App\Http\Controllers\Api\V1\BaseController` uses the `ApiResponse` trait with the unified error contract.

## Open Questions Resolved (via Clarifications)

- Supplier eligibility matching strategy (product category-derived targeting)
- Deadline enforcement semantics
- Award semantics + transactional guarantees
- Revision model (single record updated)

## Remaining Unknowns (to confirm during implementation)

- Exact supplier profile table/model name used for supplier identity (likely `SupplierProfile`)
- How supplier-to-category/product associations are represented (repo/model exploration required)
- Existing error code registry entries suitable for RFQ/quotation workflow conflicts (409)

## Implementation Notes

- Prefer small, composable queries in repositories.
- Use eager-loading on show/list endpoints.
- Apply throttling on send and submit endpoints, consistent with existing routes.
