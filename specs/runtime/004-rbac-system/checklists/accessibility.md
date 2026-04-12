# Accessibility Checklist — RBAC System

## RTL & Arabic

- [ ] Role management page fully RTL (Nuxt UI native RTL support)
- [ ] All role labels displayed in Arabic (عميل, مقاول, مهندس مشرف, مهندس ميداني, مدير النظام)
- [ ] Toast notifications for role changes in Arabic
- [ ] Error messages for unauthorized access in Arabic
- [ ] Table headers in Arabic
- [ ] Form labels and buttons in Arabic

## UI Components

- [ ] `UTable` used for user listing (keyboard navigable)
- [ ] `UModal` for role assignment (focus trap, ESC to close)
- [ ] `USelect` for role dropdown (accessible labeling)
- [ ] Loading states shown during API calls
- [ ] Empty states for no users in role filter
- [ ] Pagination controls accessible (aria-labels)

## Authorization Feedback

- [ ] Unauthorized redirect shows toast explaining why (not silent redirect)
- [ ] Hidden navigation items do not leave empty sections
- [ ] Admin-only pages show clear "access denied" if somehow reached
