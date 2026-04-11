# STAGE_01: Project Initialization — Developer Quickstart Guide

**Phase:** 01_PLATFORM_FOUNDATION  
**Status:** PLANNING  
**Date:** 2026-04-10  
**Audience:** New developers joining the project

---

## Welcome to Bunyan بنيان

This guide will get you from zero to running the Bunyan platform in **~30 minutes**. Follow the setup steps below, then run the verification checklist to confirm everything works.

---

## Prerequisites

**Required:**
- macOS, Linux, or WSL2 (Windows Subsystem for Linux)
- Git 2.37+
- 2GB free disk space
- Terminal/bash

**Option A: Docker Setup (Recommended)**
- Docker Desktop 4.0+
- Docker Compose 2.0+

**Option B: Local Setup**
- PHP 8.3 or higher
- Composer 2.x
- Node.js 20.x
- MySQL 8.0
- Redis 7

---

## Quick Setup (Docker)

### Step 1: Clone Repository

```bash
git clone https://github.com/your-org/bunyan-app.git
cd bunyan-app
```

### Step 2: Configure Environment

```bash
# Backend
cp backend/.env.example backend/.env

# Frontend
cp frontend/.env.example frontend/.env
```

### Step 3: Start Docker Services

```bash
docker-compose up -d

# Wait 10 seconds for MySQL to be ready
sleep 10
```

### Step 4: Setup Backend

```bash
# Install dependencies
cd backend && composer install

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --seed

# Back to root
cd ..
```

### Step 5: Setup Frontend

```bash
cd frontend
npm install
cd ..
```

### Step 6: Start Development Servers

**Terminal 1 - Backend:**
```bash
cd backend
php artisan serve
```

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
```

---

## Quick Setup (Local — macOS)

### Step 1-2: Clone & Configure

```bash
git clone https://github.com/your-org/bunyan-app.git
cd bunyan-app
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

### Step 3: Install Services

```bash
# MySQL (if not installed)
brew install mysql@8.0
brew services start mysql@8.0

# Redis
brew install redis
brew services start redis

# PHP 8.3 (if using Homebrew)
brew install php@8.3
brew services start php@8.3
```

### Step 4: Setup Backend

```bash
cd backend
composer install
php artisan key:generate
php artisan migrate --seed
cd ..
```

### Step 5: Setup Frontend

```bash
cd frontend
npm install
cd ..
```

### Step 6: Start Development Servers

**Terminal 1:**
```bash
cd backend && php artisan serve
```

**Terminal 2:**
```bash
cd frontend && npm run dev
```

---

## Verification Checklist

After setup, verify everything is working:

### ✅ Backend Check

```bash
cd backend

# 1. Database connection
php artisan tinker
>>> DB::connection()->getPDO()
# Should return PDOConnection object

# 2. Models loaded
>>> User::count()
# Should return a number (or 0 if fresh)

# 3. Migrations run
>>> php artisan migrate:status
# All migrations should show "Ran"

# Exit tinker
>>> exit
```

### ✅ Frontend Check

Open browser: **`http://localhost:3000`**

- ✅ Page loads without errors
- ✅ Nuxt UI components visible
- ✅ "Welcome to Bunyan" header displays
- ✅ Arabic/English locale switcher works
- ✅ Switching to Arabic (RTL) works correctly

### ✅ API Check

```bash
# In a new terminal
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+966501234567",
    "role": "customer"
  }'

# Should return:
# {
#   "success": true,
#   "data": { "id": 1, "email": "test@example.com", "token": "..." },
#   "message": "User registered successfully"
# }
```

### ✅ Database Check

```bash
# Login to MySQL
mysql -u root -p

# Show databases
mysql> SHOW DATABASES;

# Should show: bunyan

# Select and check tables
mysql> USE bunyan;
mysql> SHOW TABLES;

# Should list 13 tables: users, projects, phases, tasks, reports, workflow_configurations, etc.
```

---

## First API Call Example

### Login & Get Token

**Step 1: Register user** (if not already done)
```bash
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Contractor",
    "email": "contractor@bunyan.local",
    "password": "SecurePassword123!",
    "password_confirmation": "SecurePassword123!",
    "phone": "+966501111111",
    "role": "contractor"
  }'
```

**Step 2: Login to get token**
```bash
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "contractor@bunyan.local",
    "password": "SecurePassword123!"
  }'

# Response:
# {
#   "success": true,
#   "data": {
#     "user": { "id": 2, "name": "John Contractor", "email": "...", "role": "contractor" },
#     "access_token": "1|abcdef123456...",
#     "token_type": "Bearer"
#   },
#   "message": "Login successful"
# }
```

**Step 3: Use token to call API**
```bash
# Save token
TOKEN="1|abcdef123456..."

# List projects (filtered by role)
curl -X GET http://localhost:8000/api/v1/projects \
  -H "Authorization: Bearer $TOKEN"

# Response:
# {
#   "success": true,
#   "data": [ ... ],
#   "message": "Projects retrieved successfully"
# }
```

---

## First UI Page Example

### Login Flow

**URL:** `http://localhost:3000/auth/login`

1. **Login Form Page**
   - Arabic: "صفحة تسجيل الدخول"
   - Email field (with Arabic placeholder)
   - Password field (masked)
   - "Sign In" button

2. **Submit Credentials**
   - Email: `contractor@bunyan.local`
   - Password: `SecurePassword123!`
   - Click "Sign In"

3. **Expected Outcome**
   - Redirects to `/dashboard` (after successful auth)
   - Token stored in localStorage (check DevTools → Application → localStorage)
   - User name displayed in header (top right)

### Create Project (Contractor View)

**URL:** `http://localhost:3000/dashboard/projects/create` (after login)

1. **Form Fields** (in Arabic)
   - Project Title (عنوان المشروع)
   - Description (الوصف)
   - Budget (الميزانية)
   - Start Date (تاريخ البدء)
   - End Date (تاريخ الانتهاء)
   - Supervising Architect (dropdown)

2. **Fill & Submit**
   - Title: "مشروع البناء الحديث"
   - Budget: "250000"
   - Click "Save" (حفظ)

3. **Expected Outcome**
   - Success toast message: "Project created successfully"
   - Redirect to project detail page
   - Project appears in projects list

---

## Troubleshooting

### "Connection refused" on localhost:8000

**Problem:** Backend server not running  
**Solution:**
```bash
cd backend
php artisan serve --host=127.0.0.1 --port=8000
```

### "SQLSTATE[HY000]: General error: 1 near 'json': syntax error"

**Problem:** SQLite used instead of MySQL  
**Solution:** Ensure `.env` has:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=bunyan
DB_USERNAME=root
DB_PASSWORD=root
```

### "Nuxt app fails to start: Cannot find module @nuxt/ui"

**Problem:** Node modules not installed  
**Solution:**
```bash
cd frontend
npm install
npm run dev
```

### "Token verification failed"

**Problem:** CORS or token encoding issue  
**Solution:**
```bash
# Check Laravel CORS config
cat backend/config/cors.php

# Ensure frontend origin is allowed:
'paths' => ['api/*'],
'allowed_origins' => ['localhost:3000', 'http://localhost:3000'],
```

### "RTL not working (text still left-to-right)"

**Problem:** Locale not set to Arabic  
**Solution:**
1. Check browser console: `useI18n().locale.value` should be `'ar'`
2. Click locale switcher: "العربية" button (top right)
3. Page should reload with `<html dir="rtl">`

### "Permission denied" on docker-compose up

**Problem:** Docker daemon not running  
**Solution:**
```bash
# macOS
open /Applications/Docker.app

# Linux
sudo systemctl start docker
```

### "MySQL won't start / port 3306 in use"

**Problem:** MySQL already running from previous session  
**Solution:**
```bash
# Kill existing process
lsof -i :3306
kill -9 <PID>

# Or restart via Docker
docker-compose down
docker-compose up -d mysql
```

---

## Development Workflow

### Making Changes

1. **Create feature branch**
   ```bash
   git checkout -b feature/add-project-listing
   ```

2. **Make code changes** (backend or frontend)

3. **Run tests**
   ```bash
   # Backend
   cd backend && composer run test
   
   # Frontend
   cd frontend && npm run test
   ```

4. **Run linting**
   ```bash
   # Backend
   cd backend && composer run lint
   
   # Frontend
   cd frontend && npm run lint
   ```

5. **Commit & push**
   ```bash
   git add .
   git commit -m "feat: add project listing page"
   git push origin feature/add-project-listing
   ```

6. **Create pull request** on GitHub

### Running Tests Locally

```bash
# Backend: Unit + Feature tests
cd backend && php artisan test

# Frontend: Component + Unit tests
cd frontend && npm run test

# Frontend: E2E tests (requires backend running)
cd backend && php artisan serve &
cd frontend && npm run test:e2e
```

### Code Quality Checks

```bash
# PHP linting
cd backend && vendor/bin/pint --test

# PHP static analysis
cd backend && phpstan analyse

# Frontend linting
cd frontend && npm run lint

# Frontend TypeScript checking
cd frontend && npm run typecheck
```

---

## Project Structure Cheat Sheet

```
bunyan-app/
├── backend/                    # Laravel API
│   ├── app/
│   │   ├── Models/            # Eloquent models (User, Project, etc.)
│   │   ├── Services/          # Business logic
│   │   ├── Repositories/      # Database queries
│   │   ├── Http/Controllers/  # API controllers
│   │   ├── Http/Requests/     # Form validation
│   │   └── Policies/          # Authorization logic
│   ├── database/migrations/   # Database schema
│   ├── database/seeders/      # Test data
│   ├── tests/                 # PHPUnit tests
│   └── routes/api.php         # API routes
│
├── frontend/                   # Nuxt.js frontend
│   ├── pages/                 # Page components (auto-routed)
│   ├── components/            # Reusable components
│   ├── stores/                # Pinia stores (state management)
│   ├── composables/           # Reusable logic hooks
│   ├── locales/               # i18n translation files (ar.json, en.json)
│   ├── tests/                 # Vitest tests
│   └── nuxt.config.ts         # Nuxt configuration
│
├── docs/                       # Documentation
│   ├── ai/                    # AI governance docs
│   └── architecture/          # ADRs (architecture decisions)
│
└── specs/                      # Project specifications
    └── runtime/001-project-initialization/
        ├── spec.md            # Full specification
        ├── plan.md            # This phase's plan
        ├── research.md        # Framework research
        ├── data-model.md      # Database schema
        └── quickstart.md      # This file
```

---

## Useful Commands

### Backend (Laravel)

```bash
# Database
php artisan migrate              # Run all migrations
php artisan migrate:rollback     # Undo last migration
php artisan migrate:fresh --seed # Reset DB + re-seed
php artisan tinker               # Interactive shell

# Testing
php artisan test                 # Run all tests
php artisan test --coverage      # With coverage report

# Serving
php artisan serve --port=8000    # Start dev server
php artisan queue:work           # Start queue listener

# Code quality
vendor/bin/pint                  # Auto-fix code style (or `composer lint:fix`)
phpstan analyse                  # Static analysis
```

### Frontend (Nuxt.js)

```bash
# Development
npm run dev                      # Start dev server
npm run build                    # Build for production
npm run preview                  # Preview production build

# Testing
npm run test                     # Run unit tests
npm run test:watch               # Watch mode
npm run test:e2e                 # E2E tests
npm run test:e2e:ui              # E2E tests with UI

# Code quality
npm run lint                     # Check ESLint
npm run lint:fix                 # Auto-fix ESLint issues
npm run typecheck                # TypeScript checking
npm run format                   # Format with Prettier
```

---

## Key Contacts & Resources

- **Documentation:** `docs/` folder in repo
- **Architecture Decisions:** `docs/architecture/ADR/` folder
- **API Docs:** (will be auto-generated at `/docs/api`)
- **Issues:** GitHub Issues tab
- **Discussions:** GitHub Discussions tab

---

## What's Next?

1. ✅ Complete this quickstart
2. Read `docs/PROJECT_CONTEXT_PRIMER.md` for platform overview
3. Read `docs/DESIGN.md` for UI/UX guidelines
4. Read `docs/ai/AI_ENGINEERING_RULES.md` for coding patterns
5. Pick a task from the issue tracker and make your first contribution!

---

**Generated by:** PLAN Step  
**Status:** READY FOR USE  
**Last Updated:** 2026-04-10
