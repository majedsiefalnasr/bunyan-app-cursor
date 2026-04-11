# Contributing to Bunyan

## Before you open a pull request

1. Pull latest `develop` (or your target branch) and run **`npm run install`** if lockfiles changed.
2. From the repo root, run **`npm run validate`** (or at minimum the areas you touched: backend `composer run lint`, `composer run analyze`, `php artisan test`; frontend `npm run lint`, `npm run typecheck`, `npm run test`).
3. **PHP style** is enforced only by **Laravel Pint** (`backend/pint.json`). Do not reintroduce PHP CS Fixer or a second formatter.

## Git hooks

- **pre-commit** (lint-staged): formats/lints staged files (e.g. Pint + PHPStan on staged PHP).
- **pre-push**: runs `npm run check` (full lint, format check, typecheck, analysis).

Avoid **`git push --no-verify`** / **`git commit --no-verify`**. Use it only for a documented emergency, and fix the underlying issue in a follow-up commit.

## Environment variables

New or renamed **`backend/.env`** keys must be reflected in:

- `backend/.env.example` (documentation and new clones)
- `backend/ci.env` (CI copies this to `.env` in workflows)

## Further reading

- Local setup: [docs/SETUP.md](docs/SETUP.md)
- Architecture: [docs/architecture/](docs/architecture/)
- Deep testing notes: [specs/runtime/001-project-initialization/guides/TESTING_GUIDE.md](specs/runtime/001-project-initialization/guides/TESTING_GUIDE.md)
