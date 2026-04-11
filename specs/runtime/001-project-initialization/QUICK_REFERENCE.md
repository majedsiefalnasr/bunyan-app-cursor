# Quick Reference: Phase 1 Infrastructure Setup

**Status:** ✅ Complete | **Date:** 2026-04-10 | **Tasks:** 25/25

---

## Quick Start

### Installation

```bash
# 1. Install dependencies
npm run install

# 2. Start Docker services
npm run docker:up

# 3. Backend setup
cd backend && composer install && cp .env.example .env && php artisan key:generate

# 4. Frontend setup
cd frontend && npm install

# 5. Initialize pre-commit hooks
npx husky install
```

### Development

```bash
# Start both servers
npm run dev

# Or individually:
cd backend && php artisan serve              # http://localhost:8000
cd frontend && npm run dev                   # http://localhost:3000
```

### Validation

```bash
# Full validation pipeline (what CI runs)
npm run validate

# Quick checks
npm run lint                                 # Check violations
npm run typecheck                            # TypeScript check
npm run test                                 # Run all tests
```

---

## File Structure

### Backend

```
backend/
├── composer.json              # Dependencies
├── phpunit.xml               # Testing config
├── phpstan.neon              # Analysis config
├── .php-cs-fixer.php         # Formatting rules
├── routes/api.php            # API routes
├── app/
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic
│   ├── Repositories/         # Data access
│   ├── Policies/             # Authorization
│   ├── Http/Controllers/     # API controllers
│   └── Exceptions/           # Error handling
└── database/
    ├── migrations/           # Migrations
    └── seeders/              # Seeders
```

### Frontend

```
frontend/
├── package.json              # Dependencies
├── nuxt.config.ts            # Nuxt configuration
├── i18n.config.ts            # Internationalization
├── tsconfig.json             # TypeScript config
├── pages/                    # Route pages
├── layouts/                  # Layout components
├── components/               # Vue components
├── stores/                   # Pinia stores
├── composables/              # Vue composables
└── tests/                    # Test files
```

---

## Key Files

### Configuration

| File | Purpose |
|------|---------|
| `backend/composer.json` | PHP dependencies |
| `frontend/package.json` | Node dependencies |
| `root/package.json` | Root npm scripts |
| `docker-compose.yml` | Docker services |

### Environment

| File | Purpose |
|------|---------|
| `.env.example` | Local dev template |
| `backend/ci.env` | CI/CD environment template (`cp ci.env .env` in `backend/` on CI) |

### CI/CD

| File | Purpose |
|------|---------|
| `.github/workflows/backend-ci.yml` | Backend pipeline |
| `.github/workflows/frontend-ci.yml` | Frontend pipeline |
| `.github/workflows/pre-commit-guard.yml` | PR validation |

### Pre-Commit

| File | Purpose |
|------|---------|
| `.husky/pre-commit` | Hook runner |
| `.lintstagedrc.json` | Lint config |

---

## Common Commands

### Backend

```bash
cd backend

# Composer
composer install                # Install deps
composer update                 # Update deps

# Development
php artisan serve               # Start server
php artisan tinker              # Interactive shell
php artisan migrate             # Run migrations

# Testing
php artisan test                # Run tests
php artisan test --coverage     # With coverage

# Code Quality
vendor/bin/php-cs-fixer fix     # Fix formatting
vendor/bin/phpstan analyse      # Static analysis
```

### Frontend

```bash
cd frontend

# Installation
npm install                     # Install deps
npm update                      # Update deps

# Development
npm run dev                     # Start dev server
npm run build                   # Production build
npm run preview                 # Preview build

# Testing
npm run test                    # Unit tests
npm run test:watch             # Watch mode
npm run test:e2e               # E2E tests

# Code Quality
npm run lint                    # Check linting
npm run lint:fix               # Fix linting
npm run format                 # Format code
npm run typecheck              # Type checking
```

### Docker

```bash
# Services
docker-compose up -d            # Start services
docker-compose down             # Stop services
docker-compose logs -f          # View logs
docker-compose ps               # Service status

# From root
npm run docker:up
npm run docker:down
npm run docker:logs
```

### Git & Pre-Commit

```bash
# Initialize hooks
npx husky install

# Commit (hooks run automatically)
git add .
git commit -m "message"

# Bypass hooks (avoid!)
git commit --no-verify
```

---

## Technology Stack

### Backend
- **Framework:** Laravel 11.x
- **Language:** PHP 8.2+
- **Database:** MySQL 8.0
- **Cache:** Redis 7
- **Auth:** Laravel Sanctum
- **Testing:** PHPUnit
- **Analysis:** PHPStan
- **Format:** PHP-CS-Fixer

### Frontend
- **Framework:** Nuxt 3
- **Language:** TypeScript 5.6
- **Runtime:** Node 20 LTS
- **Styling:** Tailwind CSS v4
- **State:** Pinia
- **i18n:** Arabic (RTL) + English
- **Testing:** Vitest + Playwright
- **Format:** ESLint + Prettier

### DevOps
- **Container:** Docker + Docker Compose
- **CI/CD:** GitHub Actions
- **Hooks:** Husky + lint-staged

---

## Directory Tree

```
bunyan-app-cursor/
├── backend/                     # Laravel 11
│   ├── app/
│   ├── database/
│   ├── routes/
│   ├── tests/
│   ├── composer.json
│   └── .env.*
├── frontend/                    # Nuxt 3
│   ├── pages/
│   ├── layouts/
│   ├── components/
│   ├── stores/
│   ├── package.json
│   └── nuxt.config.ts
├── .github/workflows/           # CI/CD
├── docker-compose.yml           # Orchestration
├── Dockerfile.*                 # Container images
├── .husky/                      # Git hooks
├── .lintstagedrc.json          # Lint config
├── package.json                 # Root npm
└── specs/                       # Documentation
```

---

## Troubleshooting

### Backend Won't Start

```bash
cd backend
composer install                 # Reinstall deps
php artisan key:generate         # Generate app key
php artisan migrate              # Run migrations
```

### Frontend Won't Start

```bash
cd frontend
npm install                      # Reinstall deps
npm run dev                      # Start dev server
```

### Docker Issues

```bash
docker-compose down              # Stop all
docker system prune             # Clean up
docker-compose up -d            # Restart
```

### Pre-Commit Hook Failing

```bash
npx husky install                # Reinstall hooks
npm run lint:fix                # Fix linting issues
npm run format                  # Format code
```

---

## Environment Variables

### Backend (.env)

```env
APP_NAME="Bunyan"
APP_ENV=local
APP_DEBUG=true
DB_HOST=127.0.0.1
DB_DATABASE=bunyan
DB_USERNAME=root
REDIS_HOST=127.0.0.1
SANCTUM_STATEFUL_DOMAINS=localhost:3000
```

### Frontend (.env)

Frontend uses `.env` for build-time variables (if needed).

### Docker (.env.example)

```env
BACKEND_DB_HOST=mysql
BACKEND_DB_PORT=3306
FRONTEND_API_URL=http://localhost:8000
```

---

## Useful Links

### Documentation
- Phase 1 Completion Report: `PHASE_1_COMPLETION_REPORT.md`
- File Manifest: `PHASE_1_FILE_MANIFEST.md`
- Orchestrator Report: `PHASE_1_ORCHESTRATOR_REPORT.md`

### Configuration
- Stage Definition: `specs/phases/01_PLATFORM_FOUNDATION/STAGE_01_PROJECT_INITIALIZATION.md`
- Spec Details: `specs/runtime/001-project-initialization/spec.md`

---

## Checklist

- [ ] Dependencies installed (`npm run install`)
- [ ] Docker services running (`docker-compose up -d`)
- [ ] Backend accessible at `http://localhost:8000`
- [ ] Frontend accessible at `http://localhost:3000`
- [ ] Pre-commit hooks installed (`npx husky install`)
- [ ] Tests passing (`npm run test`)
- [ ] Linting passing (`npm run lint`)

---

## Next Phase

Ready for **Phase 2: Database Migrations & Models**

**What's Next:**
- Database migrations (13 tables)
- Eloquent models
- Repository classes
- Policy classes
- Service layer

**Estimated Time:** 16-20 hours

---

*Last Updated: 2026-04-10 | Phase: 1/6 | Status: Complete ✅*
