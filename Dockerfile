# Multi-stage production Dockerfile for Bunyan (Frontend + Backend + Nginx)
# Stage 1: Build frontend
FROM node:20-slim AS frontend-builder

WORKDIR /app/frontend

COPY ./frontend/package.json ./frontend/package-lock.json* ./

RUN apt-get update && apt-get install -y --no-install-recommends python3 make g++ && rm -rf /var/lib/apt/lists/* && \
    npm install --legacy-peer-deps

COPY ./frontend .

RUN npm run build

# Stage 2: Build backend
FROM php:8.2-fpm AS backend-builder

RUN apt-get update && apt-get install -y \
    git curl libpq-dev libzip-dev zip unzip \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
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

# Stage 3: Final runtime (PHP + Node + Nginx + Supervisor)
FROM php:8.2-fpm

# Install runtime dependencies, Nginx, Node.js, and Supervisor
RUN apt-get update && apt-get install -y \
    nginx supervisor \
    curl git libpq-dev libzip-dev zip unzip \
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

# Create necessary directories
RUN mkdir -p /var/run/php-fpm /var/log/supervisor /etc/supervisor/conf.d /var/log/nginx

# Configure PHP-FPM
RUN mkdir -p /etc/php-fpm.d && echo "[www]\nlisten = 127.0.0.1:9000\nuser = www-data\ngroup = www-data\npm = dynamic\npm.max_children = 512\npm.start_servers = 64\npm.min_spare_servers = 64\npm.max_spare_servers = 128\n" > /etc/php-fpm.d/www.conf

# Create Nginx configuration
RUN mkdir -p /etc/nginx/conf.d && cat > /etc/nginx/nginx.conf <<'NGINX_EOF'
user www-data;
worker_processes auto;
pid /var/run/nginx.pid;
error_log /var/log/nginx/error.log warn;

events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    default_type application/octet-stream;

    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for"';

    access_log /var/log/nginx/access.log main;

    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 65;
    types_hash_max_size 2048;

    include /etc/nginx/conf.d/*.conf;
}
NGINX_EOF

# Create Nginx site configuration for reverse proxy
RUN cat > /etc/nginx/conf.d/bunyan.conf <<'NGINX_SITE_EOF'
upstream backend {
    server 127.0.0.1:9000;
}

upstream frontend {
    server 127.0.0.1:3000;
}

server {
    listen 8000 default_server;
    listen [::]:8000 default_server;
    server_name _;

    # Log files
    access_log /var/log/nginx/bunyan_access.log main;
    error_log /var/log/nginx/bunyan_error.log warn;

    # API routes - proxy to PHP backend
    location ~ ^/api/ {
        proxy_pass http://backend;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # PHP routes (if any direct PHP access needed)
    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass backend;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
    }

    # Frontend - proxy to Nuxt
    location / {
        proxy_pass http://frontend;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    # Health check endpoint
    location /health {
        access_log off;
        return 200 "OK\n";
        add_header Content-Type text/plain;
    }
}
NGINX_SITE_EOF

# Remove default Nginx config
RUN rm -f /etc/nginx/sites-enabled/default

# Create startup script for initialization
RUN cat > /app/entrypoint.sh <<'ENTRYPOINT_EOF'
#!/bin/bash
set -e

echo "Starting Bunyan initialization..."

# Set permissions
chmod 755 /app/backend
chmod 755 /app/frontend

# Run database migrations and setup
echo "Setting up Laravel storage and database..."
cd /app/backend
php artisan storage:link --force || true
php artisan migrate --force
echo "Laravel setup complete!"

# Start supervisor
echo "Starting service supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/services.conf
ENTRYPOINT_EOF

RUN chmod +x /app/entrypoint.sh

# Create supervisord configuration (without backend-init)
RUN mkdir -p /etc/supervisor/conf.d && cat > /etc/supervisor/conf.d/services.conf <<'SUPERVISOR_EOF'
[supervisord]
nodaemon=true
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid
user=root

[program:php-fpm]
command=/usr/local/sbin/php-fpm --nodaemonize
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/php-fpm.err.log
stdout_logfile=/var/log/supervisor/php-fpm.out.log
priority=10

[program:nginx]
command=/usr/sbin/nginx -g "daemon off;"
autostart=true
autorestart=true
stderr_logfile=/var/log/supervisor/nginx.err.log
stdout_logfile=/var/log/supervisor/nginx.out.log
priority=20

[program:frontend]
command=/bin/sh -c "cd /app/frontend && node .output/server/index.mjs"
autostart=true
autorestart=true
environment=NODE_ENV=production,PORT=3000
stderr_logfile=/var/log/supervisor/frontend.err.log
stdout_logfile=/var/log/supervisor/frontend.out.log
priority=30
SUPERVISOR_EOF

EXPOSE 8000

ENTRYPOINT ["/app/entrypoint.sh"]
