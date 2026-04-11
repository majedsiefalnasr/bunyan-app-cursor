/**
 * lint-staged configuration for Bunyan.
 *
 * Strategy:
 * - Prettier: Markdown (non-frontend), YAML, root manifests, other declarative files
 * - Frontend: Prettier 3 + ESLint via `npm --prefix frontend exec` (correct local toolchain)
 * - Backend: Pint + PHPStan on full `backend/` when any PHP is staged (reliable analysis)
 * - SKILL.md: optional size validation when `scripts/ci/validate-skill-sizes.sh` exists
 *
 * Notes:
 * - lint-staged v10+ re-stages formatter output automatically — do not run `git add`.
 * - Avoid `bash -c "…"` with string commands for matched files: extra argv would attach to bash, not Pint.
 *
 * @type {import('lint-staged').Config}
 */
export default {
  // Prettier — Markdown outside `frontend/` (must not use `!(frontend/**/*.md)` — that matches almost all files)
  "./*.md": ["prettier --write"],
  "!(frontend)/**/*.md": ["prettier --write"],
  "*.{yml,yaml}": ["prettier --write"],

  // Prettier — `.mdc` outside `frontend/`
  "!(frontend)/**/*.mdc": ["prettier --write"],
  "./*.mdc": ["prettier --write"],

  // Root-only JSON / scripts (slash in pattern disables matchBase so we do not hit `frontend/package.json`)
  "./*.json": ["prettier --write"],
  "./*.{js,mjs,cjs,ts}": ["prettier --write"],

  // SKILL.md validation — runs when script is executable; otherwise no-op
  "**/SKILL.md": [
    'bash -c "test -x scripts/ci/validate-skill-sizes.sh && scripts/ci/validate-skill-sizes.sh || true"',
  ],

  // Frontend — declarative / docs (Prettier 3 from frontend)
  "frontend/**/*.{json,css,md,mdc}": [
    "npm --prefix frontend exec -- prettier --write",
  ],

  // Frontend — code (Prettier via npm exec; ESLint must run with `frontend/` as cwd for flat config)
  "frontend/**/*.{vue,ts,js,mjs,cjs}": [
    "npm --prefix frontend exec -- prettier --write",
    (files) =>
      `bash -lc 'cd frontend && npx eslint --max-warnings=0 --fix ${files
        .map((f) => JSON.stringify(f))
        .join(" ")}'`,
  ],

  // Backend — function form so file paths are not appended after `bash -lc` (full tree run)
  "backend/**/*.php": () => [
    'bash -lc "cd backend && vendor/bin/pint"',
    'bash -lc "cd backend && vendor/bin/phpstan analyse --memory-limit=512M"',
  ],
};
