# Multi-stage combined Dockerfile for Bunyan (Frontend + Backend)
# Stage 1: Build frontend
FROM node:20-slim AS frontend-builder

WORKDIR /app/frontend

COPY ./frontend/package.json ./frontend/package-lock.json* ./

RUN apt-get update && apt-get install -y --no-install-recommends python3 make g++ && rm -rf /var/lib/apt/lists/* && \
    npm ci --legacy-peer-deps

COPY ./frontend .

RUN npm run build

# Stage 2: Build backend
FROM php:8.2-fpm AS backend-builder

RUN apt-get update && apt-get install -y \
    git curl libpq-dev libzip-dev zip unzip \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app/backend

COPY ./backend/composer.json ./backend/composer.lock* ./

RUN composer install --no-scripts --no-autoloader

COPY ./backend .

RUN composer dump-autoload

# Stage 3: Final runtime (use PHP as base to run both)
FROM php:8.2-fpm

# Install Node.js runtime and supervisor
RUN apt-get update && apt-get install -y \
    curl supervisor \
    git libpq-dev libzip-dev zip unzip \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql zip gd \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# Create app directories
WORKDIR /app

# Copy built backend from builder stage
COPY --from=backend-builder /app/backend ./backend

# Copy built frontend from builder stage
COPY --from=frontend-builder /app/frontend/.output ./frontend/.output
COPY --from=frontend-builder /app/frontend/package.json ./frontend/package.json
COPY --from=frontend-builder /app/frontend/node_modules ./frontend/node_modules

# Copy supervisord configuration
RUN mkdir -p /etc/supervisor/conf.d

COPY <<EOF /etc/supervisor/conf.d/services.conf
[supervisord]
nodaemon=true
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:backend]
command=sh -c "cd /app/backend && php artisan storage:link --force && php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=8000"
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/backend.err.log
stdout_logfile=/var/log/supervisor/backend.out.log

[program:frontend]
command=sh -c "cd /app/frontend && node .output/server/index.mjs"
autostart=true
autorestart=true
environment=NODE_ENV=production
stderr_logfile=/var/log/supervisor/frontend.err.log
stdout_logfile=/var/log/supervisor/frontend.out.log
EOF

EXPOSE 8000 3000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/services.conf"]
