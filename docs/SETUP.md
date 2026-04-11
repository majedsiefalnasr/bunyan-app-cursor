# Local development setup

Monorepo root drives installs, Git hooks, and aggregate scripts (`package.json`).

## Requirements

| Tool     | Version | Notes                                                                                                                                       |
| -------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| PHP      | 8.2+    | Extensions used in CI: `pdo`, `pdo_mysql`, `mbstring`, `xml`, `bcmath`, `tokenizer`, `json`, `redis` (optional locally if you stub drivers) |
| Composer | 2.6+    |                                                                                                                                             |
| Node.js  | 20 LTS  | Matches GitHub Actions                                                                                                                      |
| npm      | 10+     |                                                                                                                                             |
| Git      | 2.30+   |                                                                                                                                             |

**Databases:** MySQL 8 and Redis 7 are expected for full-stack runs (`docker-compose` or local installs). PHPUnit uses SQLite in memory by default for tests; see `backend/phpunit.xml`.

## First-time install

```bash
git clone <repository-url> && cd <repo-root>

# Installs backend (composer) + frontend (npm); runs `husky` via prepare
npm run install

cp .env.example .env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

cd backend && php artisan key:generate && cd ..
```

Use `npm run docker:up` if you prefer Docker for MySQL/Redis; align `backend/.env` `DB_*` and `REDIS_*` with `docker-compose.yml`.

## Sanity check

```bash
npm run validate
```

Backend-only bar before a PHP change:

```bash
cd backend && composer run lint && composer run analyze && php artisan test
```

- **Lint** is Laravel Pint (`pint.json`). Auto-fix: `composer run lint:fix`.
- **`php artisan test --parallel`** with coverage needs **Xdebug** or **PCOV** on your PHP. If you see “No code coverage driver”, run `php artisan test` without coverage/parallel locally, or install a coverage extension.

## Environment parity

When you add or rename Laravel env variables, update **`backend/.env.example`** and **`backend/ci.env`** so local setups and GitHub Actions stay aligned.
