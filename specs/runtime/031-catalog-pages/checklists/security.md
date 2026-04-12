# Catalog Pages — Security Checklist

- [ ] No catalog page bypasses Nuxt `auth` where the backing API requires Sanctum
- [ ] Filter values coerced server-side (existing Laravel rules); client sends plain query params only
- [ ] No secrets or tokens logged from catalog composables
- [ ] XSS: product/category names rendered as text (Vue default), not `v-html`
