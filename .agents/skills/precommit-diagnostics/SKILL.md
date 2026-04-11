---
name: precommit-diagnostics
description: Pre-commit/pre-push failure diagnostics
---

# Pre-commit Diagnostics — Bunyan

## Pre-commit Hooks

### Backend (PHP)

```bash
# Laravel Pint — formatting
vendor/bin/pint --test

# PHPStan — static analysis
phpstan analyse

# PHPUnit — quick tests
php artisan test --parallel
```

### Frontend (JS/TS)

```bash
# ESLint
eslint --fix .

# TypeScript
nuxt typecheck

# Vitest
vitest run
```

## Common Failures & Fixes

### Laravel Pint

- **Fix**: Run `composer run lint:fix` (or `vendor/bin/pint`) to auto-format
- **Prevention**: Configure IDE to format on save; `pint.json` is the source of truth

### PHPStan Level Errors

- **Fix**: Add proper type annotations, fix return types
- **Prevention**: Use strict types (`declare(strict_types=1)`)

### ESLint

- **Fix**: Run `npm run lint:fix` for auto-fixable issues
- **Prevention**: Configure IDE ESLint integration

### TypeScript Errors

- **Fix**: Add missing types, fix type mismatches
- **Prevention**: Enable strict mode in tsconfig

## Bypass Rules

- `--no-verify` is **FORBIDDEN** in normal workflow
- Emergency bypass requires documented justification
- All bypasses must be addressed in next commit
