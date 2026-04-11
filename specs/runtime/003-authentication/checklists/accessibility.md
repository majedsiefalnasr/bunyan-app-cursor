# Accessibility Checklist — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Created:** 2026-04-11T00:00:00Z

## Form Accessibility

- [ ] All form inputs have associated `<label>` elements
- [ ] Error messages linked to inputs via `aria-describedby`
- [ ] Required fields marked with `aria-required="true"`
- [ ] Form submission errors announced to screen readers
- [ ] Focus moved to first error field on validation failure

## Keyboard Navigation

- [ ] All form elements reachable via Tab key
- [ ] Submit button activatable via Enter key
- [ ] Focus visible (Bunyan focus ring: `hsla(212, 100%, 48%, 1)`)
- [ ] Logical tab order (top to bottom, RTL-aware)
- [ ] No keyboard traps on any auth page

## RTL / Arabic

- [ ] All auth pages render correctly in RTL direction
- [ ] Form labels aligned to the right
- [ ] Input text direction respects RTL
- [ ] Error messages display in Arabic
- [ ] Navigation links and buttons positioned per RTL layout
- [ ] Password visibility toggle icon positioned correctly in RTL

## Visual

- [ ] Sufficient color contrast (WCAG AA — 4.5:1 for body text)
- [ ] Error states use color + icon (not color alone)
- [ ] Loading states clearly visible
- [ ] Links distinguishable from body text (underline or color)
- [ ] Touch targets minimum 44x44px on mobile
