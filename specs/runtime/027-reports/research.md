# Research — STAGE_27

## Laravel 11

- Form Requests: `authorize()` returns true when route already guarded; optional explicit `Gate::define` skipped for MVP admin middleware.
- DomPDF: `Pdf::loadView($view, $data)->output()` for binary response.
- Maatwebsite Excel v3: `Excel::download(new ExportClass, 'name.xlsx')` per package docs.

## Route Ordering

Register `analytics/reports/{type}` **before** any generic `{id}` routes under same prefix if added later; current admin group has no collision.

## Nuxt UI v4

- Use `USelect`, `UButton`, `UInput` for filters; `UTable` for rows from API `data.rows`.
