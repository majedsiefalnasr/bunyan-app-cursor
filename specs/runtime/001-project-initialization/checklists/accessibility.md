# STAGE_01: Project Initialization — Web Accessibility (WCAG 2.1 AA) Checklist

**Phase:** 01_PLATFORM_FOUNDATION  
**Generated:** 2026-04-10  
**Status:** SPECIFYING

---

## Executive Summary

This checklist ensures Bunyan Platform meets **WCAG 2.1 Level AA** accessibility standards from Day 1, supporting users with disabilities across all roles (Customer, Contractor, Architect, Field Engineer, Admin).

---

## 1. Perceivable Content

### 1.1 Text Alternatives (Images, Icons, Media)

- [ ] All images have descriptive `alt` text:
  - Good: `<img alt="Project budget overview for Q1 2026" src="...">`
  - Bad: `<img alt="image" src="...">` or `<img src="...">` (missing alt)

- [ ] Decorative images marked with `alt=""`: `<img alt="" src="decorative-divider.png">`
- [ ] Icon buttons have `aria-label`: `<button aria-label="Close modal">✕</button>`
- [ ] SVG icons have `<title>`: `<svg><title>Project Status Icon</title>...</svg>`
- [ ] Videos have captions: `<video><track kind="captions" src="captions.vtt"></video>` (Phase 02 for video support)
- [ ] Audio content has transcripts linked on page

### 1.2 Adaptable Content (Responsive & Reflow)

- [ ] No horizontal scrolling at 320px viewport width (mobile)
- [ ] Text readable at 200% zoom without loss of functionality
- [ ] Zoom does not break layout (test with browser zoom)
- [ ] Responsive design: mobile-first approach with media queries
- [ ] Grid/flexbox layouts reflow on narrow screens (no fixed widths)

### 1.3 Distinguishable Content (Contrast, Color, Fonts)

#### Color Contrast

- [ ] Text contrast ratio ≥ **4.5:1** for normal text (WCAG AA)
- [ ] Text contrast ratio ≥ **3:1** for large text (18px+ or 14px bold+)
- [ ] UI component contrast ≥ **3:1** (buttons, borders, focus indicators)
- [ ] Test contrast: WebAIM Contrast Checker, Axe DevTools

- [ ] Color not sole means of conveying information:
  - Bad: "Approved (green), Pending (yellow), Rejected (red)" only
  - Good: "✓ Approved (green), ⏳ Pending (yellow), ✗ Rejected (red)"

- [ ] Design system colors meet contrast targets:
  - [ ] Text (#171717) on white (#ffffff): 21:1 ✓
  - [ ] Text (#171717) on light gray (#f0f0f0): 18:1 ✓
  - [ ] Accent colors tested against backgrounds

#### Font & Text Readability

- [ ] Primary font: Geist (accessible, sans-serif)
- [ ] Font size minimum: **14px** for body text (ideally 16px)
- [ ] Line height minimum: **1.5** for body text
- [ ] Letter spacing (Geist uses tighter spacing): monitor readability for dyslexic users
- [ ] Monospace font (Geist Mono) for code: readable at 12px+

### 1.4 Audio/Video Controls

- [ ] Video player keyboard controls (play, pause, seek, volume)
- [ ] Autoplay disabled: `<video autoplay>` ❌ / `<video>` + user-initiated play ✓
- [ ] Audio does not auto-play on page load
- [ ] Captions/subtitles toggleable (Phase 02)

---

## 2. Operable Interface

### 2.1 Keyboard Accessibility

- [ ] **All functionality keyboard accessible** (not mouse-only):
  - Forms: Tab through fields, Enter to submit
  - Modals: Tab cycles through focusable elements, Escape closes
  - Dropdowns: Arrow keys select, Enter confirms
  - Data tables: Tab through rows, Arrow keys navigate cells

- [ ] Tab order logical: left-to-right, top-to-bottom (matches visual order)
  - Test: Tab repeatedly through page, verify focus order makes sense
  - Fix: adjust `tabindex` if needed (use `tabindex="-1"` to skip)

- [ ] No keyboard traps: users can Tab out of any element
  - Test: Tab through modal, verify Tab/Shift+Tab escape
  - Fix: ensure modal focuses on first input, last button returns to start

- [ ] Shortcut keys:
  - [ ] Ctrl+S: save (form), Cmd+S (Mac)
  - [ ] Esc: close modal/cancel
  - [ ] Enter: submit form, open link
  - [ ] Arrow keys: navigate lists, tables, date pickers
  - Document shortcuts in help page or tooltip

### 2.2 Focus Indicators

- [ ] Visible focus indicator on all interactive elements:
  ```css
  button:focus-visible {
    outline: 2px solid #007bff;
    outline-offset: 2px;
  }
  ```

- [ ] Focus indicator color contrast ≥ **3:1** against background
- [ ] Focus indicator visible on keyboard navigation (not just mouse)
- [ ] Do NOT remove focus outline without providing alternative indicator
  - Bad: `outline: none` (removes accessibility)
  - Good: `outline: 2px solid blue` (visible outline)

- [ ] Focus visible on:
  - [ ] Links
  - [ ] Buttons
  - [ ] Form inputs
  - [ ] Checkboxes, radio buttons
  - [ ] Modals (focus management)
  - [ ] Dropdowns
  - [ ] Tables (row/cell focus)

### 2.3 Motion & Animation

- [ ] Animations respect `prefers-reduced-motion` preference:
  ```css
  @media (prefers-reduced-motion: reduce) {
    * { animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important; }
  }
  ```

- [ ] Animations do not flash/flicker more than 3x per second (can trigger seizures)
- [ ] Parallax effects disabled for motion-sensitive users
- [ ] Carousel/auto-play: pause button accessible, tab through slides

### 2.4 Seizure Prevention (Photosensitivity)

- [ ] No content flashes more than 3 times per second
- [ ] If animations used, test with photosensitive users (Phase 02)

---

## 3. Understandable Content

### 3.1 Readable Text & Language

- [ ] Language clearly identified:
  - HTML: `<html lang="ar">` for Arabic, `<html lang="en">` for English
  - Part of page in different language: `<span lang="en">English phrase</span>` in Arabic text

- [ ] Text readability:
  - Sentence structure: simple, clear
  - Avoid jargon: explain technical terms
  - Reading level: target 8th-grade (use Flesch Reading Ease score ≥ 60)

- [ ] Arabic text formatting:
  - Right-to-left text: `dir="rtl"` on container
  - Text alignment: `text-align: right` or Tailwind `text-end`
  - Logical properties: `ms` (margin-start) instead of `ml` (margin-left)

- [ ] Abbreviations explained on first use:
  - First: "Construction Quality Assurance (CQA)"
  - After: "CQA team"

### 3.2 Predictable Behavior

- [ ] Navigation consistent across pages:
  - Header, sidebar, footer in same location
  - Main navigation order same on all pages
  - User menu in same spot

- [ ] Form behavior predictable:
  - Submit button label clear: "Save Project" not "OK"
  - Error messages appear near affected field
  - Required fields marked clearly (red asterisk + `required` attribute)
  - Form resets on successful submission or provides confirmation

- [ ] Links predictable:
  - Link text describes destination: "View Project Details" not "Click Here"
  - External links marked: `<a href="..." aria-label="External link"> or icon
  - No blank targets without warning: `target="_blank" aria-label="Opens in new tab"`

### 3.3 Input Assistance & Error Recovery

- [ ] **Form Errors:**
  - Error messages clear and specific: "Email must be valid" not "Invalid input"
  - Error associated with field: `aria-describedby="error-email"`
  - Error message in `id="error-email"` near input
  - Error container has `role="alert"` for screen reader announcement

  ```html
  <div role="alert" id="error-email">
    Email must be a valid email address.
  </div>
  <input aria-describedby="error-email" id="email" type="email" />
  ```

- [ ] **Validation Feedback:**
  - Real-time validation optional, but clear
  - Validation messages appear before form submission (helpful)
  - Successful validation indicated: checkmark or green border

- [ ] **Input Requirements:**
  - Required fields marked: `<input required>`
  - Instructions clear: "Phone format: +20 (Egypt)" + placeholder `placeholder="+20 XXX XXX XXXX"`
  - Format hints in label or near field

- [ ] **Recovery:**
  - Users can undo actions: "Undo delete project" link after deletion
  - Confirmation dialogs for destructive actions: "Are you sure?"
  - Data not lost on submission failure (preserve form state)

---

## 4. Robust Markup & Technical Standards

### 4.1 Valid HTML & ARIA

- [ ] HTML validates with W3C validator (zero errors)
  - Test: https://validator.w3.org/
  - No duplicate IDs, proper nesting, valid attributes

- [ ] Semantic HTML used:
  - `<button>` not `<div onclick>` for buttons
  - `<a>` for links, not `<span>` or `<div>`
  - `<nav>`, `<main>`, `<section>`, `<article>`, `<aside>` for structure
  - `<form>` + `<label for="input-id">` for forms
  - `<table>` + `<th>`, `<td>` for tables (not layout)

- [ ] ARIA used correctly (only when semantic HTML insufficient):
  - `aria-label`: button with icon-only label
  - `aria-describedby`: form field with long description
  - `aria-expanded`: collapsible sections, menus
  - `aria-hidden="true"`: decorative elements, skip by screen reader
  - `role="alert"`: important messages, status updates

### 4.2 Form Structure

- [ ] Every form input has `<label>`:
  ```html
  <label for="email">Email Address</label>
  <input id="email" type="email" required />
  ```

- [ ] Input types correct: `type="email"`, `type="tel"`, `type="date"`, `type="number"`, etc.
- [ ] Fieldsets group related inputs:
  ```html
  <fieldset>
    <legend>Project Dates</legend>
    <label>Start Date: <input type="date" /></label>
    <label>End Date: <input type="date" /></label>
  </fieldset>
  ```

- [ ] Error messages associated: `aria-describedby="error-id"`
- [ ] Help text associated: `aria-describedby="help-id"`

### 4.3 Heading Structure

- [ ] Headings use semantic hierarchy:
  - `<h1>` page title (one per page)
  - `<h2>` main sections
  - `<h3>` subsections
  - No skipping levels: `<h1>` → `<h2>` → `<h3>` (not `<h1>` → `<h3>`)

- [ ] Headings describe content accurately (not styled divs)
- [ ] Test with screen reader: headings navigable (H key in NVDA)

### 4.4 Data Tables Accessibility

- [ ] Table structure:
  ```html
  <table>
    <thead>
      <tr>
        <th>Project Name</th>
        <th>Budget</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Project A</td>
        <td>$50,000</td>
        <td>Active</td>
      </tr>
    </tbody>
  </table>
  ```

- [ ] Table headers marked: `<th scope="col">` (column) or `<th scope="row">` (row)
- [ ] Caption for complex tables: `<caption>Project List Q1 2026</caption>`
- [ ] Summary attribute optional but helpful for complex tables

### 4.5 Responsive Images (Picture Element)

- [ ] Responsive images with `<picture>`:
  ```html
  <picture>
    <source media="(max-width: 600px)" srcset="image-small.webp">
    <source media="(max-width: 1200px)" srcset="image-medium.webp">
    <img src="image-large.jpg" alt="Project overview">
  </picture>
  ```

- [ ] `srcset` attribute for pixel density: `srcset="image.jpg 1x, image@2x.jpg 2x"`
- [ ] `alt` text always provided

---

## 5. Screen Reader Compatibility

### 5.1 Screen Reader Testing

- [ ] Test with NVDA (Windows, free) or JAWS (paid)
- [ ] Test with Mac VoiceOver (built-in)
- [ ] Test with iOS/Android screen readers

### 5.2 Semantic Structure for Screen Readers

- [ ] Page landmark roles identified:
  ```html
  <header role="banner">...</header>
  <nav aria-label="Main navigation">...</nav>
  <main role="main">...</main>
  <aside aria-label="Sidebar">...</aside>
  <footer role="contentinfo">...</footer>
  ```

- [ ] List structures preserved: `<ul>`, `<ol>`, `<li>` (not divs styled as lists)
- [ ] Buttons vs. links: `<button>` for actions, `<a>` for navigation
- [ ] Decorative elements hidden: `aria-hidden="true"`

### 5.3 ARIA Live Regions

- [ ] Dynamic content updates announced: `aria-live="polite"` or `aria-live="assertive"`
  - Polite: announce after current speech finishes
  - Assertive: announce immediately (high priority)

  ```html
  <div aria-live="polite" aria-atomic="true">
    Project saved successfully!
  </div>
  ```

- [ ] Status messages: `aria-live="status"`
- [ ] Error alerts: `role="alert"` (equivalent to `aria-live="assertive"`)

---

## 6. Mobile & Touch Accessibility

### 6.1 Touch Target Size

- [ ] Minimum touch target: **48px × 48px** (WCAG 2.5 guidance)
- [ ] Buttons, links, form inputs meet minimum size
- [ ] Spacing between targets: minimum 8px gap
- [ ] Test: tap with finger on real mobile device

### 6.2 Zoom & Orientation

- [ ] Page scalable: `<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">`
- [ ] Do NOT disable zoom: `user-scalable=no` ❌
- [ ] Content accessible in portrait AND landscape orientation
- [ ] No content hidden on rotation

### 6.3 Mobile Form Input

- [ ] Correct input types for context:
  - Email: `type="email"` → shows @ keyboard
  - Phone: `type="tel"` → shows numeric keyboard
  - Number: `type="number"` → shows numeric keyboard
  - Date: `type="date"` → shows date picker

- [ ] Touch-friendly form labels (large, centered)
- [ ] Mobile error messages visible (not cut off by keyboard)

---

## 7. Content-Specific Accessibility

### 7.1 Form-Heavy Pages (Project Creation, Report Submission)

- [ ] Multi-step forms: indicator of progress
  - Step 1 of 4, Step 2 of 4, etc.
  - Visual + text indicator

- [ ] Form labels clear and persistent
- [ ] Required fields marked: `<input required>` + visual indicator
- [ ] Validation summary at top: list all errors with links to fields
- [ ] Successful submission confirmation page

### 7.2 Tables & Data Visualization

- [ ] Data tables have proper heading structure
- [ ] Sortable columns: button label includes sort direction (e.g., "Budget, ascending")
- [ ] Filterable columns: filter controls keyboard accessible
- [ ] Paginated tables: page number buttons labeled

### 7.3 Navigation & Menus

- [ ] Skip to main content link: `<a href="#main">Skip to content</a>` (visible on Tab)
- [ ] Menu keyboard navigation: arrow keys, Escape to close
- [ ] Current page indicator in menu: `aria-current="page"`

### 7.4 Modals & Dialogs

- [ ] Modal focus management: focus moves to modal on open, returns on close
- [ ] Escape key closes modal
- [ ] Close button labeled clearly: `aria-label="Close modal"` or visible text
- [ ] Modal announced: `role="dialog"` + `aria-labelledby="modal-title"`

---

## 8. RTL Accessibility (Arabic-Specific)

### 8.1 RTL Layout

- [ ] HTML `dir="rtl"` on root for Arabic
- [ ] CSS logical properties used: `ms` (margin-start), `me` (margin-end), `ps`, `pe`, `start`, `end`
- [ ] Flexbox/Grid layouts respond to `dir` attribute (no hardcoded `flex-direction`)

### 8.2 Arabic Content Best Practices

- [ ] Arabic fonts support character combining (e.g., diacritics for Quran text)
- [ ] Font: system default or Geist (supports Arabic)
- [ ] Numbers: Western (0-9) and Eastern Arabic (٠-٩) both supported
- [ ] Date format: localized (Arabic: "10 أبريل 2026" not "2026-04-10")
- [ ] Phone numbers: +20 (Egypt), +966 (Saudi), etc., with country code

### 8.3 RTL Testing

- [ ] Test all pages in both Arabic (RTL) and English (LTR)
- [ ] Verify layout flipping: sidebars, buttons, tables, forms
- [ ] Verify screen reader announces direction correctly
- [ ] Test RTL form input: text direction, cursor position

---

## 9. Testing & Validation

### 9.1 Automated Accessibility Testing

- [ ] Run Axe DevTools on all pages: zero critical/serious issues
- [ ] Run WAVE browser extension: check for errors/warnings
- [ ] Lighthouse audit (Chrome DevTools): Accessibility score ≥ 90
- [ ] Automated testing in CI: `npm run test:a11y` (using jest-axe or similar)

### 9.2 Manual Testing

- [ ] Test with keyboard only: navigate entire site without mouse
- [ ] Test with screen reader (NVDA on Windows, VoiceOver on Mac)
- [ ] Test on real mobile device with touch
- [ ] Test zoom at 200%
- [ ] Test with color blindness simulator (Sim Daltonism, Color Oracle)

### 9.3 Accessibility Checklist Template

Create `specs/runtime/001-project-initialization/reports/ACCESSIBILITY_AUDIT.md`:

- [ ] Manual keyboard navigation test — PASS/FAIL
- [ ] Screen reader test (NVDA/JAWS) — PASS/FAIL
- [ ] Mobile touch test — PASS/FAIL
- [ ] 200% zoom test — PASS/FAIL
- [ ] Axe DevTools scan — PASS/FAIL (zero critical)
- [ ] WAVE scan — PASS/FAIL
- [ ] Lighthouse audit — PASS (≥90)
- [ ] Color contrast — PASS (all elements ≥4.5:1)
- [ ] Focus indicators — PASS (all interactive elements)
- [ ] Form labels — PASS (all fields have labels)
- [ ] Heading hierarchy — PASS (proper structure)

---

## 10. Inclusive Design Principles

### 10.1 User Perspectives

Test with users who have:
- [ ] Blindness (screen reader users)
- [ ] Low vision (magnification users, high contrast mode)
- [ ] Color blindness (protanopia, deuteranopia, tritanopia)
- [ ] Motor disabilities (keyboard-only, slow/jerky movements)
- [ ] Cognitive disabilities (simple language, clear structure)
- [ ] Deafness (captions, transcripts, visual alerts)
- [ ] Dyslexia (readable fonts, clear hierarchy, sufficient whitespace)

### 10.2 Plain Language

- [ ] Use simple words: "help" not "assistance", "use" not "utilize"
- [ ] Short sentences: average 15-20 words
- [ ] Active voice: "Users can create projects" not "Projects can be created"
- [ ] Avoid idioms and cultural references

### 10.3 Responsive Design

- [ ] Mobile-first approach
- [ ] Flexible layouts (flexbox, grid)
- [ ] Scalable typography (rem units, not px)
- [ ] Touch-friendly target sizes (≥48px)

---

## 11. Compliance & Reporting

### 11.1 WCAG 2.1 Level AA Conformance

- [ ] All checkpoints above addressed
- [ ] Automated testing: ≥90 Lighthouse score
- [ ] Manual testing: PASS
- [ ] Accessibility statement included: link on footer

### 11.2 Accessibility Statement

Add to footer or `/accessibility` page:

```
Accessibility Statement

This website is committed to accessibility for all users, 
including those with disabilities. We follow WCAG 2.1 Level AA standards.

Accessibility Features:
- Keyboard navigation
- Screen reader compatible
- High contrast mode support
- Resizable text (Zoom to 200%)
- Arabic and English (RTL support)

Feedback:
If you experience accessibility issues, please contact us at accessibility@bunyan.com
```

### 11.3 Accessibility Audit Report

Document findings in `specs/runtime/001-project-initialization/reports/ACCESSIBILITY_AUDIT.md`:

- Tested pages and user journeys
- WCAG 2.1 AA conformance level achieved
- Known limitations (if any)
- Remediation plan for non-conformant elements
- Testing tools and methodology used
- Date of audit

---

## 12. Checklist Completion Summary

**All items are mandatory for STAGE_01 completion:**

- [ ] **Perceivable Content:** 4 items (alt text, adaptable, contrast, audio/video)
- [ ] **Operable Interface:** 4 items (keyboard, focus, motion, seizure prevention)
- [ ] **Understandable Content:** 3 items (readable, predictable, input assistance)
- [ ] **Robust Markup:** 5 items (valid HTML, forms, headings, tables, images)
- [ ] **Screen Reader Compatibility:** 3 items (testing, semantic structure, live regions)
- [ ] **Mobile & Touch:** 3 items (touch target, zoom, mobile input)
- [ ] **Content-Specific:** 4 items (forms, tables, navigation, modals)
- [ ] **RTL Accessibility:** 3 items (RTL layout, Arabic, testing)
- [ ] **Testing & Validation:** 3 items (automated, manual, checklist)
- [ ] **Inclusive Design:** 3 items (user perspectives, plain language, responsive)
- [ ] **Compliance & Reporting:** 3 items (conformance, statement, audit report)

**Total Accessibility Checklist Items: 42**

---

**Generated by:** CLARIFY Step  
**Date:** 2026-04-10  
**Status:** ACTIVE (Ready for PLAN → IMPLEMENT)

---

## References

- **WCAG 2.1:** https://www.w3.org/WAI/WCAG21/quickref/ (Quick Reference)
- **ARIA Authoring Practices:** https://www.w3.org/WAI/ARIA/apg/
- **WebAIM:** https://webaim.org/ (Web Accessibility Testing, Resources)
- **Deque Axe DevTools:** https://www.deque.com/axe/devtools/
- **WAVE:** https://wave.webaim.org/ (Browser Extension)
- **Color Blindness Simulator:** https://www.color-blindness.com/coblis-color-blindness-simulator/
