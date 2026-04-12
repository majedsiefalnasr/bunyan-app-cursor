# Research — Categories

## Laravel

- Adjacency list pattern with `parent_id` and eager in-memory tree build (single `Category::query()` + PHP nest) keeps read path predictable for moderate tree sizes.
- Soft deletes on `categories` align with catalog entities.
- `Str::slug()` for default English slug; Arabic names stored separately in `name_ar`.

## Nuxt UI / Vue

- Nuxt UI `UCard`, `UButton`, `UModal`, `UFormGroup`, `UInput`, `UToggle` for admin forms.
- Drag-and-drop: native HTML5 `draggable` on sibling rows to avoid new dependencies; `@vueuse/core` already present for helpers if needed.

## RTL

- Tree indentation uses `ps-*` (padding-inline-start) for RTL-safe depth visual.
