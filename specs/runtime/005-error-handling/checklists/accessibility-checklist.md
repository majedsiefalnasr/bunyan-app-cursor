# STAGE_05: Error Handling & Logging — Accessibility Checklist

**Phase:** 01_PLATFORM_FOUNDATION  
**Generated:** 2026-04-11  
**Status:** SPECIFYING  
**Purpose:** Ensure error messages, pages, and notifications are accessible to all users including those with disabilities

---

## 1. Error Message Clarity & Readability

**Objective:** Error messages are clear, concise, and easy to understand.

### 1.1 Message Clarity Standards

- [ ] Error messages avoid jargon:

  - [ ] "VALIDATION_ERROR" → user sees "البيانات غير صحيحة" (clear, not technical)
  - [ ] "RBAC_ROLE_DENIED" → user sees "غير مصرح لك بهذا الإجراء" (explain, don't code)
  - [ ] No error codes shown to users (only shown in console for debugging)

- [ ] Messages are actionable:

  - [ ] Not: "An error occurred"
  - [ ] Yes: "حقل الاسم مطلوب. يرجى إدخال الاسم" (field name required. please enter name)
  - [ ] Validation errors show which field failed and why

- [ ] Messages avoid blame:

  - [ ] Not: "You didn't fill the form correctly"
  - [ ] Yes: "البيانات التالية مفقودة: [fields]" (the following data is missing)

- [ ] Language is consistent:
  - [ ] All error messages in Arabic (primary) with English fallback
  - [ ] Same terms used across app (e.g., "الميزانية" for budget everywhere)
  - [ ] No mixing of Arabic/English in single message

### 1.2 Message Format for Accessibility

- [ ] Structured validation errors:

  - [ ] Each field error listed separately
  - [ ] Not: "Fields name, email, budget invalid"
  - [ ] Yes: List format with each field on new line or bullet
  - [ ] Example in error response details (section 1.3, lines 89-93)

- [ ] Hints vs. errors:
  - [ ] Validation hint: "الميزانية يجب أن تكون أكبر من 1000 ريال"
  - [ ] Error summary: "فشل التحقق من صحة البيانات. يرجى تصحيح المشاكل التالية:"
  - [ ] User knows what to fix before re-submitting

---

## 2. Arabic/RTL Support in Error Handling

**Objective:** All error components properly support right-to-left Arabic text.

### 2.1 HTML Direction & Language

- [ ] HTML language and direction:

  - [ ] `<html dir="rtl" lang="ar">` set in nuxt.config.ts (section 9.3, line 447)
  - [ ] Test: Inspect page source → `dir="rtl"` present
  - [ ] Test: Page displays RTL layout

- [ ] Dynamic error messages:
  - [ ] When error displays dynamically, RTL still applied
  - [ ] Toast notifications inherit RTL from parent
  - [ ] Error boundary component respects RTL

### 2.2 Tailwind RTL Support

- [ ] Logical properties instead of physical:

  - [ ] Use `text-start` (left in LTR, right in RTL) not `text-left`
  - [ ] Use `me-4` (margin-end) not `mr-4` (margin-right)
  - [ ] Use `ps-4` (padding-start) not `pl-4` (padding-left)
  - [ ] Use `border-e` (border-end) not `border-r` (border-right)
  - [ ] Test: Error pages display correct alignment

- [ ] Text alignment in error components:

  - [ ] Error card text centered or start-aligned
  - [ ] Button text centered
  - [ ] Descriptions right-aligned (start in RTL)

- [ ] Icon positioning for RTL:
  - [ ] Error icon positioned correctly (left side in RTL)
  - [ ] Arrow icons flip direction if needed
  - [ ] Test: Error boundary icon position in RTL

### 2.3 Arabic-Specific Typography

- [ ] Arabic font rendering:

  - [ ] Geist Sans supports Arabic (or fallback like Segoe UI)
  - [ ] Arabic text renders smoothly without broken characters
  - [ ] Diacritical marks (tashkeel) display correctly
  - [ ] Test: Error message with diacriticals displays correctly

- [ ] Text direction with numbers/English:

  - [ ] Mixed Arabic-English text handles bidirectional flow
  - [ ] Example: "خطأ 500" (error 500) displays correctly
  - [ ] Number '123' doesn't break RTL text flow

- [ ] Form field labels in Arabic:
  - [ ] Validation error field labels Arabic
  - [ ] Example: field name "الاسم" (name), error "حقل الاسم مطلوب"
  - [ ] All labels Arabic-first

---

## 3. Screen Reader Compatibility

**Objective:** Error messages and pages are fully accessible to screen readers.

### 3.1 Error Boundary Component

- [ ] Semantic HTML structure:

  - [ ] Error card uses `<div role="alert">` or `<section>`
  - [ ] Heading uses `<h1>` (not styled div)
  - [ ] Description uses `<p>` (not div)
  - [ ] Buttons are actual `<button>` elements

- [ ] Screen reader announcements:

  - [ ] Error title announced first ("حدث خطأ")
  - [ ] Then description
  - [ ] Then available actions (button labels)
  - [ ] Test: Screen reader announces full error flow

- [ ] Error code visibility:
  - [ ] Error code shown in UI for debugging
  - [ ] But marked with `aria-hidden="true"` to avoid noise
  - [ ] Or placed in `<details>` tag for optional expansion

### 3.2 Toast Notifications

- [ ] Toast role and region:

  - [ ] Toast container has `role="region"` or `role="alert"`
  - [ ] Toast has `aria-live="polite"` (announces when added)
  - [ ] Toast has `aria-label` describing the message
  - [ ] Test: Screen reader announces toast immediately

- [ ] Toast content clarity:

  - [ ] Toast title: error code (or friendly name)
  - [ ] Toast description: full error message
  - [ ] Toast actions labeled clearly (e.g., "أعد المحاولة")

- [ ] Multiple toast handling:
  - [ ] When toast queued, screen reader notified
  - [ ] "Additional error: [code]" announced (don't repeat full message)
  - [ ] Queue size not announced (avoid clutter)

### 3.3 Error Pages (404, 500, 403)

- [ ] Page heading hierarchy:

  - [ ] Page title in `<h1>` (not h2 or styled div)
  - [ ] Example: `<h1>الصفحة غير موجودة</h1>` (Page not found)
  - [ ] Subheading in `<h2>` if present

- [ ] Page structure:

  - [ ] Use `<main>` for primary content
  - [ ] Descriptive text in `<p>` tags
  - [ ] Links and buttons properly labeled
  - [ ] Test: Screen reader describes page structure

- [ ] Page skip links:
  - [ ] "Skip to main content" link if needed
  - [ ] Or main error content is first focusable element
  - [ ] Keyboard navigation works efficiently

---

## 4. Keyboard Navigation

**Objective:** All error components fully navigable via keyboard.

### 4.1 Keyboard Focus Management

- [ ] Error boundary component:

  - [ ] Error card is in natural tab order
  - [ ] Buttons are focusable (`<button>` elements)
  - [ ] Focus trap optional (or allow escape to parent)
  - [ ] Test: Tab through error card, all buttons reachable

- [ ] Error pages (404, 500, 403):

  - [ ] Page loads with focus on main heading or first action
  - [ ] Tab order follows visual order (LTR/RTL)
  - [ ] Tab cycles back to start after last element
  - [ ] Shift+Tab goes backward
  - [ ] Test: Tab through page, focus visible

- [ ] Toast notifications:
  - [ ] Toast actions (retry button) focusable
  - [ ] Close button focusable and labeled
  - [ ] Escape key closes toast (if modal behavior)

### 4.2 Focus Indicators

- [ ] Focus ring visibility:

  - [ ] All interactive elements show focus ring when tabbed
  - [ ] Focus ring color: `hsla(212, 100%, 48%, 1)` (blue, from DESIGN.md)
  - [ ] Focus ring at least 2px width
  - [ ] Test: Tab through page, see blue focus ring on each button

- [ ] Focus ring contrast:

  - [ ] Focus ring contrasts with background
  - [ ] Not: blue ring on blue background
  - [ ] Test: Focus ring visible on light and dark backgrounds

- [ ] Focus not hidden:
  - [ ] `:focus-visible` used (not `:focus` alone)
  - [ ] No `outline: none` without replacement
  - [ ] No invisible focus indicator

### 4.3 Keyboard Shortcuts in Error UI

- [ ] Button keyboard equivalents:

  - [ ] Enter/Space activates buttons
  - [ ] Test: Focus on "أعد المحاولة" button, press Enter → retries

- [ ] Escape key handling:

  - [ ] Escape closes error boundary (if modal)
  - [ ] Escape closes toast (if dismissible)
  - [ ] Escape doesn't silently fail

- [ ] Error page navigation:
  - [ ] Links can be followed with Enter
  - [ ] Buttons can be activated with Enter/Space
  - [ ] No keyboard traps (user not stuck)

---

## 5. Color Contrast & Color Independence

**Objective:** Error messages visible and not reliant on color alone.

### 5.1 Text Contrast

- [ ] Error message text contrast:

  - [ ] Body text (16px+): min 4.5:1 ratio (WCAG AA)
  - [ ] Large text (18px+ or 14px bold+): min 3:1 ratio
  - [ ] Test: Use color contrast checker (WebAIM)

- [ ] Toast notification contrast:

  - [ ] Toast title contrast: 4.5:1
  - [ ] Toast description contrast: 4.5:1
  - [ ] Toast background + text: sufficient contrast
  - [ ] Example: red text on white bg, or white text on red bg

- [ ] Error page contrast:

  - [ ] Heading contrast: 4.5:1
  - [ ] Body text contrast: 4.5:1
  - [ ] Test error pages:
    - [ ] 404 page: gray "404" + black heading → sufficient
    - [ ] 500 page: gray "500" + black heading → sufficient
    - [ ] 403 page: gray "403" + black heading → sufficient

- [ ] Button contrast:
  - [ ] Button text on button background: 4.5:1
  - [ ] Example: white text on black button → 21:1 ✓
  - [ ] Example: black text on gray button → test with tool

### 5.2 Color Not Sole Indicator

- [ ] Error severity indication:

  - [ ] Not: red background only = error
  - [ ] Yes: red background + "خطأ" label + icon
  - [ ] Toast uses color (red) + text ("Error") + icon

- [ ] Validation error indication:

  - [ ] Not: red outline on field only
  - [ ] Yes: red outline + error text + icon (if needed)
  - [ ] Error message explicitly states problem

- [ ] Status indicators:
  - [ ] Toast with clock icon + text "Retry in 5s" (not just color)
  - [ ] Error page with text "الصفحة غير موجودة" (not just 404 number)

### 5.3 Dark Mode Compatibility

- [ ] Light/dark theme contrast:
  - [ ] If dark mode supported, test contrast in both modes
  - [ ] Error messages visible in light mode: 4.5:1
  - [ ] Error messages visible in dark mode: 4.5:1
  - [ ] Test: Toggle dark mode, verify contrast

---

## 6. Error Boundary Accessibility

**Objective:** Error boundary component (AppErrorBoundary.vue) is fully accessible.

### 6.1 Component Structure

- [ ] Semantic layout:

  ```vue
  <div v-if="errorState.hasError" role="alert" class="...">
    <div>
      <UIcon name="..." class="..." />
    </div>
    <h1>{{ 'error_occurred' | translate }}</h1>
    <p>{{ errorState.error?.message }}</p>
    <div>
      <UButton @click="resetError">العودة</UButton>
      <UButton @click="location.reload()">تحديث الصفحة</UButton>
    </div>
  </div>
  ```

  - [ ] `role="alert"` so screen readers announce
  - [ ] Heading is `<h1>` (not div with role)
  - [ ] Buttons are `<button>` (not div with role)

- [ ] Icon accessibility:
  - [ ] Icon has `aria-hidden="true"` (decorative)
  - [ ] Icon color sufficient contrast
  - [ ] Icon description in heading or button

### 6.2 Error Details Display

- [ ] Error details optional:

  - [ ] Error code shown in small section
  - [ ] Example: `<p class="text-xs"><strong>كود الخطأ:</strong> {{ errorState.errorCode }}</p>`
  - [ ] Not prominently displayed (noise to screen readers)

- [ ] Stack trace (dev only):
  - [ ] Not shown in error boundary UI
  - [ ] Only visible in browser console
  - [ ] Or in collapsed section (user manually expands)

---

## 7. Error Page Components

**Objective:** Error pages (404.vue, 500.vue, 403.vue) are accessible.

### 7.1 Page Structure & Headings

- [ ] Page hierarchy:

  - [ ] Main heading in `<h1>` describing error (e.g., "الصفحة غير موجودة")
  - [ ] No skipped heading levels (no h3 before h2)
  - [ ] Descriptive text in `<p>` tags
  - [ ] Test: Headings list in screen reader shows correct hierarchy

- [ ] Large error numbers:

  - [ ] "404", "500", "403" displayed as large visual text
  - [ ] But marked as decorative (not screen reader content)
  - [ ] Example: `<p class="text-9xl" aria-hidden="true">404</p>`

- [ ] Main content:
  - [ ] Content wrapped in `<main>` tag
  - [ ] Or page is only content
  - [ ] Header/footer not needed for error pages

### 7.2 Error Page Links & Buttons

- [ ] Button labels clear:

  - [ ] "العودة إلى لوحة التحكم" (Go to Dashboard) - descriptive
  - [ ] "الصفحة الرئيسية" (Home) - descriptive
  - [ ] "أعد المحاولة" (Retry) - descriptive
  - [ ] Not: "Click here" or generic buttons

- [ ] Link purpose clear:

  - [ ] `to="/dashboard"` button labeled "العودة إلى لوحة التحكم"
  - [ ] `to="/"` button labeled "الصفحة الرئيسية"
  - [ ] Screen reader user knows where link goes

- [ ] Button focus:
  - [ ] First button (primary action) focused on page load
  - [ ] Or focus on page heading
  - [ ] Tab order follows visual order

---

## 8. Notification Accessibility

**Objective:** Toast notifications (useErrorNotification) are accessible.

### 8.1 Toast Announcements

- [ ] Live region configuration:

  - [ ] Toast container has `aria-live="polite"` (announce, don't interrupt)
  - [ ] `aria-atomic="true"` (announce whole toast, not just change)
  - [ ] Test: Screen reader announces toast when appears

- [ ] Toast content structure:

  - [ ] Title: error code name
  - [ ] Description: error message
  - [ ] Actions: buttons (if any)
  - [ ] Example: "[VALIDATION_ERROR] البيانات غير صحيحة. [Retry] button"

- [ ] Multiple toasts:
  - [ ] Each toast announced separately
  - [ ] User not overwhelmed (queue, don't stack)
  - [ ] Screen reader user knows error, can act on it

### 8.2 Toast Actions Accessibility

- [ ] Retry button:

  - [ ] Label: "أعد المحاولة" (Retry)
  - [ ] Focused and keyboard-accessible
  - [ ] Announces loading state if retrying
  - [ ] Example: `aria-busy="true"` during retry

- [ ] Close button:

  - [ ] Visible close button (not click-anywhere)
  - [ ] Label: "إغلاق" (Close) or "✕" with `aria-label`
  - [ ] Easily reachable (not hidden by other elements)

- [ ] Toast duration:
  - [ ] Auto-dismiss after timeout
  - [ ] But screen reader user has time to read (5-8 seconds)
  - [ ] Or option to pause auto-dismiss (button or keyboard)

---

## 9. Validation Error Accessibility

**Objective:** Form validation errors clearly indicate which fields failed and why.

### 9.1 Field Error Association

- [ ] Field labels associated with errors:

  - [ ] Each field has `<label for="field_id">الاسم</label>`
  - [ ] Error message has `id="field_id_error"`
  - [ ] Field has `aria-describedby="field_id_error"`
  - [ ] Screen reader: "Name, edit text, required, [error message]"

- [ ] Error message structure:

  - [ ] Clear which field has error
  - [ ] Clear what's wrong
  - [ ] Clear how to fix (if possible)
  - [ ] Example: "حقل الاسم مطلوب. يرجى إدخال الاسم" (Name field required)

- [ ] Multiple field errors:
  - [ ] Each field's error listed
  - [ ] Or summary at top + field errors
  - [ ] Screen reader user aware of all problems

### 9.2 Form Submission Feedback

- [ ] Submission failure notification:

  - [ ] Toast or alert announces submission failed
  - [ ] Error summary provided
  - [ ] First field error focused (or error summary)

- [ ] Field error highlighting:
  - [ ] Red border on field (visual + structural)
  - [ ] Error icon (visual feedback)
  - [ ] Error message below field (text feedback)
  - [ ] Not: color only

---

## 10. Internationalization & Accessibility

**Objective:** Arabic translations are accessible, not just translations.

### 10.1 Arabic-Specific Accessibility Issues

- [ ] Diacritical marks:

  - [ ] Tashkeel (diacritics) display correctly
  - [ ] Screen readers pronounce correctly with diacritics
  - [ ] Example: "اسم" vs "أسْم" (different pronunciation)

- [ ] Arabic abbreviations:

  - [ ] Error codes ("VALIDATION_ERROR") always in English
  - [ ] Messages in Arabic, not abbreviated
  - [ ] Example: not "خ.خ" for خطأ خادم, always "خطأ في الخادم"

- [ ] Bidi (bidirectional) text:
  - [ ] Mixed Arabic-English handled correctly
  - [ ] Example: "Error 500: خطأ في الخادم"
  - [ ] Text direction explicit (dir="rtl" on text container)

### 10.2 Localization Accessibility

- [ ] Language switching:

  - [ ] Switch from Arabic to English updates error messages
  - [ ] Screen reader announcements in current language
  - [ ] Error pages in both languages available

- [ ] Fallback language:
  - [ ] If Arabic translation missing → English shown
  - [ ] Not: untranslated key or error
  - [ ] User sees readable message

---

## 11. Testing Checklist

**Objective:** Verify accessibility before release.

### 11.1 Automated Testing

- [ ] axe Accessibility Checker:

  - [ ] Run on error pages (404, 500, 403)
  - [ ] Run on error boundary component
  - [ ] Run on toast notifications
  - [ ] Zero critical/serious violations

- [ ] Lighthouse accessibility audit:

  - [ ] Error pages score 90+ on accessibility
  - [ ] Errors detailed in Lighthouse report

- [ ] Color contrast tools:
  - [ ] WebAIM contrast checker: all text 4.5:1 or 3:1
  - [ ] Run on error text, buttons, backgrounds

### 11.2 Manual Testing

- [ ] Screen reader testing (NVDA/JAWS on Windows, VoiceOver on Mac):

  - [ ] Error boundary: Heading announced, error message read, buttons labeled
  - [ ] Toast: Toast announcement, title + description + actions
  - [ ] Error pages: Full page structure readable
  - [ ] Validation errors: Each field error associated

- [ ] Keyboard navigation (Tab, Shift+Tab, Enter, Escape):

  - [ ] All buttons reachable via Tab
  - [ ] Focus indicator visible
  - [ ] Buttons activatable with Enter/Space
  - [ ] Escape closes modal/toast (if applicable)

- [ ] RTL/Arabic testing:

  - [ ] Error pages display RTL correctly
  - [ ] Arabic text renders without broken characters
  - [ ] Buttons aligned correctly in RTL
  - [ ] Icons positioned correctly in RTL

- [ ] Color blind testing:
  - [ ] Error indicators visible without color
  - [ ] Use Coblis or similar simulator
  - [ ] Red-blind user still sees error indication

---

## 12. Completion Criteria

**All accessibility requirements must be met before STAGE_05 accessibility audit:**

- [ ] All error messages clear and actionable
- [ ] Arabic/RTL fully supported in error components
- [ ] Screen reader announces all errors and actions
- [ ] Keyboard navigation works for all interactive elements
- [ ] Text contrast meets WCAG AA (4.5:1 for body text)
- [ ] Color not sole indicator of error state
- [ ] Focus indicators visible and high contrast
- [ ] Error pages have proper heading hierarchy
- [ ] Validation errors associated with form fields
- [ ] Toast notifications announced via aria-live
- [ ] No keyboard traps in error components
- [ ] axe and Lighthouse audits pass

---

## 13. Testing Commands

```bash
# Run accessibility audit
npx axe-core frontend/components/common/AppErrorBoundary.vue

# Lighthouse audit
lighthouse http://localhost:3000/404 --view

# Arabic RTL validation
npm run test -- tests/accessibility/rtl.test.ts

# Screen reader testing
# Windows: NVDA + Firefox (free)
# Mac: VoiceOver (built-in, Cmd+F5)
# Chrome: Chromevox extension

# Contrast checker
# WebAIM: https://webaim.org/resources/contrastchecker/
# Paste hex colors, verify 4.5:1 ratio
```

---

**Last Updated:** 2026-04-11  
**Owner:** Platform Engineering  
**Accessibility Lead:** [To be assigned]  
**Related:** STAGE_05_ERROR_HANDLING.md, STAGE_05_ERROR_HANDLING/spec.md
