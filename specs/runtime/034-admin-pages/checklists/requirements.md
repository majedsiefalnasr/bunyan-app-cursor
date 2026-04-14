# Requirements Checklist — Admin Pages

> **Stage:** Admin Pages (`034-admin-pages`)
> **Generated (UTC):** 2026-04-14T13:58:31Z

## Frontend Routing & RBAC (UX Gate)

- [ ] `/admin/**` routes exist per scope list
- [ ] Admin-only Nuxt route middleware applied to all `/admin/**` pages
- [ ] Non-admin users are redirected away from `/admin/**`
- [ ] Admin layout used consistently across admin pages

## API Integration

- [ ] All data fetching uses frontend API client composables (REST only)
- [ ] List pages support pagination
- [ ] Server-side validation errors displayed via standard error contract

## UI / UX (Nuxt UI + DESIGN.md)

- [ ] Nuxt UI components used (`UDashboardLayout`, `UTable`, `UForm`, etc.)
- [ ] RTL layout verified on all pages
- [ ] Loading/empty/error states present on list + detail pages

## Testing

- [ ] Vitest: composables/state logic (pagination, filters, permission toggles)
- [ ] Playwright: admin access allowed; non-admin access redirected
