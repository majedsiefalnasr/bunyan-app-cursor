# STAGE_01: Project Initialization — Performance Optimization Checklist

**Phase:** 01_PLATFORM_FOUNDATION  
**Generated:** 2026-04-10  
**Status:** SPECIFYING

---

## Executive Summary

This checklist ensures Bunyan Platform meets performance targets from Day 1 through optimized database queries, caching strategies, asset optimization, and frontend bundle management.

---

## 1. Database Query Optimization

### 1.1 Eager Loading & N+1 Prevention

- [ ] All `belongsTo()` relationships eager-loaded:
  - Example: `Project::with('customer', 'contractor', 'supervisor')->get()` (not lazy-loaded in loop)
- [ ] All `hasMany()` and `hasManyThrough()` relationships use `with()`:
  - Example: `Project::with('phases.tasks.reports')->get()`
- [ ] Collection methods avoid N+1: use `pluck()`, `find()`, `each()` instead of loops with individual queries
- [ ] Add performance comment above queries that load related data: `// Eager-load to prevent N+1`
- [ ] Test N+1 detection: Laravel Debugbar query count checker

### 1.2 Indexing Strategy

- [ ] **Primary keys indexed** (default: id)
- [ ] **Foreign keys indexed:** customer_id, contractor_id, supervisor_id, project_id, phase_id, task_id, assigned_to, etc.
- [ ] **Query filters indexed:** `created_at`, `updated_at`, `status`, `role` (User)
- [ ] **Compound indexes** for common queries:
  - [ ] `projects (customer_id, status, created_at)` — list customer projects by date
  - [ ] `phases (project_id, status, order)` — list phases in order
  - [ ] `tasks (phase_id, status, assigned_to)` — list tasks by phase and assignee
  - [ ] `transactions (project_id, type, created_at)` — financial reports
- [ ] **Unique indexes:** email, SKU (products), order_number (orders)
- [ ] **Full-text search index** on products name/description (Phase 02 optimization)

### 1.3 Query Optimization Patterns

- [ ] Paginate all list endpoints: `->paginate(15)` (not load entire table)
- [ ] Limit query results: `->limit(100)` for safety
- [ ] Use `select()` to fetch only required columns:
  - Example: `User::select('id', 'name', 'email', 'role')->where('role', 'contractor')->get()`
- [ ] Use Eloquent `scopes` for reusable filters:
  - Example: `Project::active()->forCustomer($userId)->paginate()`
- [ ] Database query caching: Results cached for 5-15 minutes depending on entity
  - [ ] Projects: 15 minute TTL
  - [ ] Phases: 10 minute TTL
  - [ ] Tasks: 5 minute TTL
  - [ ] Products: 30 minute TTL (rarely change)

### 1.4 Database Connection Optimization

- [ ] Connection pooling configured (Phase 02, mark as deferred)
- [ ] Single connection per request lifecycle (default Laravel behavior)
- [ ] Transaction batching for bulk operations: `DB::transaction()` wrapper
- [ ] Avoid long-running database locks (timeout: 60 seconds)

---

## 2. Caching Strategy

### 2.1 Application-Level Caching

- [ ] Cache driver configured: `CACHE_DRIVER=redis` (production) or `array` (local development)
- [ ] Cache tags used for invalidation groups:
  ```php
  Cache::tags(['projects', "project.$projectId"])->put("project.$projectId", $project, now()->addMinutes(15));
  Cache::tags(['projects'])->flush(); // Invalidate all project caches
  ```

### 2.2 Entity Cache Warming

- [ ] **High-traffic entities cached on creation/update:**
  - [ ] User profiles: 30 minute TTL
  - [ ] Project details: 15 minute TTL
  - [ ] Product catalog: 30 minute TTL
  - [ ] Workflow configurations: 60 minute TTL (rarely change)

- [ ] **Cache invalidation triggers:**
  - [ ] On update: flush related cache tags
  - [ ] On delete: remove from cache
  - [ ] Example: `ProjectService::update()` calls `Cache::tags(['projects', "project.$id"])->flush()`

### 2.3 Query Result Caching

- [ ] Frequently-accessed queries cached:
  - [ ] `User::whereRole('contractor')->count()` — admin dashboard widget (1 hour TTL)
  - [ ] `Project::active()->count()` — statistics (1 hour TTL)
  - [ ] `Category::all()` — product filters (2 hour TTL)

- [ ] Cache key format: `{entity}:{context}:{identifier}`
  - Example: `projects:customer:123` for customer's projects

### 2.4 Frontend Cache Headers (HTTP Caching)

- [ ] Static assets cache: `Cache-Control: public, max-age=31536000` (1 year, include file hash in URL)
- [ ] API responses cache: `Cache-Control: private, max-age=300` (5 minutes, per-user)
- [ ] Dynamic content no-cache: `Cache-Control: no-store, must-revalidate` (auth pages)

---

## 3. Response Optimization

### 3.1 API Response Size

- [ ] Limit response payload size: **< 1 MB** per response
- [ ] Paginated responses: 15-50 items per page (configurable)
- [ ] Exclude unnecessary fields: use API Resources to select only required fields

  ```php
  // ProjectResource - only return needed fields
  'id', 'title', 'budget', 'status', 'customer_name', 'contractor_name'
  // Exclude: description, detailed audit logs, raw timestamps
  ```

- [ ] Nested relationships limited to 2 levels deep
  - Level 1: `projects.with('customer')`
  - Level 2: `projects.with('phases.tasks')`
  - Avoid: `projects.with('phases.tasks.reports.photos')` (huge payload)

### 3.2 Field Filtering & Sparse Fieldsets

- [ ] Query parameter `?fields=id,name,budget` supported on list endpoints
- [ ] API Resources implement `when($request->filled('fields'))` to filter columns
- [ ] Reduces payload by 40-60% for large result sets

### 3.3 Compression

- [ ] Gzip compression enabled on web server: `gzip on; gzip_min_length 1024;`
- [ ] Frontend bundle gzipped and served with `.gz` variant
- [ ] API responses gzipped if > 1 KB

---

## 4. Frontend Bundle Optimization

### 4.1 Bundle Size Targets

- [ ] **Main bundle:** < 250 KB (gzipped)
- [ ] **Chunk bundles:** < 100 KB each (lazy-loaded pages)
- [ ] **Total initial JS:** < 400 KB (gzipped)
- [ ] **Total initial CSS:** < 50 KB (gzipped)

### 4.2 Code Splitting & Lazy Loading

- [ ] Each page route lazy-loaded: `defineAsyncComponent(() => import('~/pages/dashboard.vue'))`
- [ ] Heavy components lazy-loaded (modals, tables, charts):
  - [ ] ProjectTable (lazy-loaded on dashboard)
  - [ ] ReportForm (lazy-loaded in task detail page)
  - [ ] ProductCatalog (lazy-loaded in store)

- [ ] Vendor bundle split: separate `@nuxt/ui` and heavy libraries
- [ ] TreeShaking enabled: remove unused code (default Nuxt 3 behavior)

### 4.3 Asset Optimization

- [ ] Images optimized:
  - [ ] PNG: max 500 KB (pre-compressed)
  - [ ] JPEG: max 250 KB (quality 80)
  - [ ] WebP format: primary, fallback to JPEG
  - [ ] Use `nuxt-image` component for responsive images

- [ ] CSS optimization:
  - [ ] Tailwind v4 used (pruned in production)
  - [ ] Unused CSS removed by Tailwind
  - [ ] Global CSS < 100 KB

- [ ] Fonts optimized:
  - [ ] Geist fonts subsetted (only used characters)
  - [ ] Font-display: `swap` (show text immediately)
  - [ ] Preload critical fonts: `<link rel="preload" as="font" href="geist.woff2">`

### 4.4 Build & Runtime Optimization

- [ ] Production build minified: `npm run build`
- [ ] Source maps disabled in production
- [ ] Tree-shaking: remove dead code paths
- [ ] Terser configured for maximum compression

---

## 5. Core Web Vitals & Performance Metrics

### 5.1 Lighthouse Metrics (Target)

- [ ] **Largest Contentful Paint (LCP):** < 2.5 seconds
- [ ] **First Input Delay (FID):** < 100 milliseconds
- [ ] **Cumulative Layout Shift (CLS):** < 0.1
- [ ] **Overall Lighthouse Score:** ≥ 90 (Performance)

### 5.2 Real User Metrics (RUM)

- [ ] Time to Interactive (TTI): < 3.5 seconds
- [ ] First Contentful Paint (FCP): < 1.8 seconds
- [ ] Speed Index: < 3.5 seconds

### 5.3 Performance Monitoring

- [ ] Add WebVitals tracking: Google Analytics 4 with Web Vitals extension (Phase 02)
- [ ] Monitor API response times: target < 200ms for 95th percentile
- [ ] Track error rates: target < 0.1%

---

## 6. Backend Performance Optimization

### 6.1 Queue Configuration

- [ ] Heavy operations offloaded to queues:
  - [ ] Report file processing (image resizing, video encoding)
  - [ ] Email notifications
  - [ ] PDF generation (invoices, reports)
  - [ ] Data exports

- [ ] Queue driver: `QUEUE_CONNECTION=redis` (production) or `sync` (development)
- [ ] Job timeout: 60 seconds (adjust for long tasks)
- [ ] Job retry: 3 times before failure
- [ ] Dead letter queue: failed jobs stored in `failed_jobs` table

### 6.2 Database Connection Management

- [ ] Connection pooling (Phase 02 enhancement)
- [ ] Read replicas (Phase 02 enhancement, not needed for STAGE_01)
- [ ] Query timeout: 60 seconds max

### 6.3 HTTP Response Compression

- [ ] Gzip enabled on web server
- [ ] Brotli enabled (if supported by infrastructure)
- [ ] Compression level: 6 (balance speed/ratio)

### 6.4 API Optimization Patterns

- [ ] Batch API endpoints to reduce requests:
  - [ ] `POST /api/v1/projects/batch` (create multiple projects)
  - [ ] `PATCH /api/v1/projects/batch` (update multiple)

- [ ] Request deduplication: cache duplicate requests within 1 second
- [ ] Webhook deduplication: idempotency keys for webhook handlers

---

## 7. Infrastructure & Deployment Performance

### 7.1 Server Configuration

- [ ] PHP opcache enabled: `opcache.enable=1`
- [ ] Opcache memory: 256 MB
- [ ] Max execution time: 60 seconds
- [ ] Max input time: 60 seconds
- [ ] Memory limit: 512 MB

### 7.2 Redis Configuration (Cache & Queue)

- [ ] Redis maxmemory policy: `maxmemory-policy allkeys-lru` (evict least recently used)
- [ ] Redis persistence: `save 900 1` (snapshot every 15 min if ≥1 change)
- [ ] Connection pooling in Redis: `TCP_BACKLOG=256`

### 7.3 Load Testing & Capacity Planning

- [ ] Load test setup: Apache JMeter or Locust configuration
- [ ] Test scenarios:
  - [ ] 100 concurrent users
  - [ ] 1000 requests per second
  - [ ] Sustained for 5 minutes
- [ ] Target response time: 95th percentile < 1 second
- [ ] Target error rate: < 0.1%

---

## 8. Monitoring & Performance Alerting

### 8.1 Performance Metrics to Monitor

- [ ] API response times (p50, p95, p99)
- [ ] Database query times
- [ ] Cache hit ratio (target > 80%)
- [ ] Queue job processing time
- [ ] Error rates and exceptions
- [ ] Memory usage (PHP, Redis)
- [ ] CPU usage

### 8.2 Alerting Thresholds

- [ ] API response time > 500ms: warning
- [ ] API response time > 2 seconds: critical alert
- [ ] Error rate > 1%: warning
- [ ] Cache hit ratio < 60%: investigate
- [ ] Queue backlog > 1000 jobs: alert

### 8.3 Performance Dashboard

- [ ] Create Grafana dashboard (Phase 02):
  - [ ] Response time trends
  - [ ] Error rates
  - [ ] Cache metrics
  - [ ] Database metrics
  - [ ] Queue metrics

---

## 9. Testing & Benchmarking

### 9.1 Performance Testing

- [ ] Baseline performance metrics established (before optimization)
- [ ] After each optimization, measure improvement
- [ ] Regression tests: ensure performance doesn't degrade on new features

### 9.2 Load Test Scenarios

- [ ] **Scenario 1: Login Load** — 100 concurrent logins
- [ ] **Scenario 2: Project List** — 100 concurrent users fetching project list
- [ ] **Scenario 3: Report Submission** — 50 concurrent file uploads
- [ ] **Scenario 4: Admin Dashboard** — 50 concurrent admin users fetching statistics

### 9.3 Performance Profiling Tools

- [ ] Laravel Debugbar: query count, memory, render time
- [ ] Telescope: request logging, database performance
- [ ] Horizon: queue job monitoring (Phase 02)
- [ ] SPX: PHP profiler for CPU/memory analysis

---

## 10. Optimization Priorities (Phased)

### Phase 1 (STAGE_01) - Must Have

- [x] Eager loading & N+1 prevention
- [x] Database indexing
- [x] Query optimization patterns
- [x] Caching strategy (Redis + tags)
- [x] API response optimization
- [x] Frontend bundle size targets
- [x] Core Web Vitals targets
- [x] Backend performance patterns (queues, compression)

### Phase 2 - Nice to Have

- [ ] Connection pooling
- [ ] Read replicas
- [ ] Advanced caching (Redis Cluster)
- [ ] CDN integration
- [ ] Batch API endpoints
- [ ] Performance monitoring (Grafana, APM)
- [ ] Load test automation

---

## 11. Checklist Completion Summary

**All Phase 1 items are mandatory for STAGE_01 completion:**

- [ ] **Database Optimization:** 4 items (eager loading, indexing, query patterns, connections)
- [ ] **Caching Strategy:** 4 items (app-level, entity warming, query caching, HTTP headers)
- [ ] **Response Optimization:** 3 items (response size, field filtering, compression)
- [ ] **Frontend Bundle:** 4 items (bundle size targets, code splitting, asset optimization, build optimization)
- [ ] **Core Web Vitals:** 3 items (Lighthouse metrics, RUM, performance monitoring)
- [ ] **Backend Optimization:** 4 items (queues, connection management, compression, API patterns)
- [ ] **Infrastructure:** 3 items (server config, Redis config, load testing)
- [ ] **Monitoring & Alerting:** 3 items (metrics, thresholds, dashboard)
- [ ] **Testing & Benchmarking:** 3 items (performance testing, load scenarios, profiling tools)

**Total Performance Checklist Items (Phase 1): 33**

---

**Generated by:** CLARIFY Step  
**Date:** 2026-04-10  
**Status:** ACTIVE (Ready for PLAN → IMPLEMENT)
