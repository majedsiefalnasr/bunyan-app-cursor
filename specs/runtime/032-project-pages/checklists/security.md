# Project Pages — Security Checklist

- [x] Project routes keep `auth` middleware aligned with Sanctum-backed APIs
- [x] Team invite and workflow actions rely on server authorization (no role spoofing client-side)
- [x] Estimates worksheet stores numbers only in `sessionStorage` (no PII)
- [x] Dynamic text rendered without `v-html` for project/task names
