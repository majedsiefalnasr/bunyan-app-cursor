# PR Summary — STAGE_09 Suppliers

## Title

feat(catalog): supplier profiles, verification, and product linkage (STAGE_09)

## Description

Implements supplier profiles for contractor users with admin verification, public verified catalog endpoints, optional `supplier_id` on products, and Nuxt pages (`/suppliers`, `/suppliers/:id`, `/suppliers/register`, `/admin/suppliers`). Adds focused PHPUnit feature coverage.

## How to test

See `specs/runtime/009-suppliers/guides/TESTING_GUIDE.md`.

## Risk

MEDIUM — new public endpoints and RBAC surface; mitigated by verified-only default catalog and role middleware on writes.

## SpecKit

Runtime: `specs/runtime/009-suppliers/` — workflow state **PRODUCTION READY** on branch `spec/009-suppliers`.
