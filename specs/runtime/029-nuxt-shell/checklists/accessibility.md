# Accessibility Checklist — Nuxt Shell

> **Phase:** 07_FRONTEND_APPLICATION
> **Created:** 2026-04-11T00:00:00Z

## Navigation

- [ ] `<nav>` landmark on `UNavigationMenu` and `UNavigationTree`
- [ ] `aria-current="page"` on the active navigation item
- [ ] `aria-label` on navigation landmarks (e.g., `aria-label="القائمة الرئيسية"` / `"Main navigation"`)
- [ ] Breadcrumb wrapped in `<nav aria-label="مسار التنقل">` / `"Breadcrumb"`
- [ ] All nav items keyboard-focusable (Tab key cycles through items)
- [ ] Enter/Space activates nav items
- [ ] Dropdown menus (`UDropdownMenu`) close on Escape key

## Mobile Drawer

- [ ] `UDrawer` has `aria-modal="true"` when open
- [ ] Focus trapped inside drawer when open
- [ ] First focusable element in drawer receives focus on open
- [ ] Escape key closes drawer
- [ ] Background scroll locked when drawer is open
- [ ] Close button has `aria-label="إغلاق القائمة"` / `"Close menu"`

## Interactive Controls

- [ ] RTL/LTR toggle button has `aria-label` describing current state
- [ ] Dark mode toggle has `aria-label` describing current mode
- [ ] Language switcher has `aria-label="اختيار اللغة"` / `"Language selection"`
- [ ] User avatar button has `aria-label="قائمة المستخدم"` / `"User menu"`
- [ ] All icon-only buttons have visible `aria-label` or `title`

## Loading & Feedback

- [ ] `UProgress` loading bar has `role="progressbar"` and `aria-label`
- [ ] Toast notifications (`UNotification`) use `role="alert"` or `aria-live="polite"`
- [ ] Skeleton placeholders have `aria-busy="true"` on parent container
- [ ] Error page `UAlert` has `role="alert"`

## RTL Accessibility

- [ ] `dir` attribute set correctly on `<html>` — screen readers use this for pronunciation
- [ ] Text directionality correct for mixed AR/EN content (bidi text handled)
- [ ] Tab order follows visual order in both RTL and LTR modes

## Color & Contrast

- [ ] Text contrast ratio ≥ 4.5:1 in both light and dark modes
- [ ] Focus ring visible in both light and dark modes (`--ds-focus-color`)
- [ ] Active nav item indicator visible without relying solely on color
