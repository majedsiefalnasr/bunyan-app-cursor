# Research — Invoicing

## Laravel 11

- Policies auto-discovered when named `ModelPolicy` alongside models.
- `Mail::to()->send()` with `Mailable` for invoice notification.
- Package `barryvdh/laravel-dompdf` — `Pdf::loadView()->output()` for binary PDF in controller.

## ZATCA Simplified QR (Phase 1)

- TLV format: each chunk `[tag (1 byte)][length (1 byte)][value (UTF-8 bytes)]`.
- Tags 1–5 for seller name, VAT number, timestamp, total with VAT, VAT amount (strings per spec examples).

## Nuxt 3

- `useApi().apiFetch` for authenticated JSON and blob handling (`responseType: 'blob'` for PDF download via `$fetch.raw` pattern if needed).

## Prior Art in Repo

- `OrderService`, `OrderPolicy`, `OrderController` patterns for RBAC and thin controllers.
- Standard API envelope via `BaseController` / `ApiResponse` trait.
