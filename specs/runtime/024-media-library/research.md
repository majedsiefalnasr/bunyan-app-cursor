# Research — Media Library

## Laravel 11

- Filesystem: `Storage::disk()`, `putFileAs`, `delete`, `exists`.
- Scheduling: `Schedule::call` or job in `bootstrap/app.php` using `->withSchedule` if used in this repo — verify `routes/console.php` pattern in project.

## Sanctum

- `auth:sanctum` middleware already applied to protected API group.

## Nuxt 3 / Nuxt UI

- File input + drag-and-drop via native `input type="file"` and `@drop` on container; `UButton`, `UCard` for layout per DESIGN.md alignment with existing dashboard pages.

## PHP GD

- `extension_loaded('gd')` guard; `imagecreatetruecolor`, `imagecopyresampled` for proportional resize to max 400px.
