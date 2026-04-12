# Catalog Pages — Security Checklist

- [x] No catalog page bypasses Nuxt `auth` where the backing API requires Sanctum
- [x] Filter values coerced server-side (existing Laravel rules); client sends plain query params only
- [x] No secrets or tokens logged from catalog composables
- [x] XSS: product/category names rendered as text (Vue default), not `v-html`
