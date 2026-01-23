# =============================================================================
# Personal Financial Tracker - Render Deployment Dockerfile
# =============================================================================
# Optimized Dockerfile for deploying to Render.com
# Includes both PHP-FPM and Nginx in a single container
# =============================================================================

# =============================================================================
# Stage 1: Composer Dependencies
# =============================================================================
FROM composer:2.6 AS composer

WORKDIR /app

# Copy composer files
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --optimize-autoloader \
    --prefer-dist \
    --no-interaction

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --classmap-authoritative

# =============================================================================
# Stage 2: Node.js Build
# =============================================================================
FROM node:20-alpine AS node

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install ALL dependencies (including dev dependencies needed for build)
RUN npm ci

# Copy source files
COPY . .

# Build assets
RUN npm run build

# =============================================================================
# Stage 3: Production Runtime (Render Optimized)
# =============================================================================
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    tzdata \
    bash

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Set working directory
WORKDIR /var/www/html

# Copy application from composer stage
COPY --from=composer /app .

# Copy built assets from node stage
COPY --from=node /app/public/build ./public/build

# Copy configuration files
COPY docker/php/php.prod.ini /usr/local/etc/php/conf.d/99-production.ini
COPY docker/nginx/render.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/scripts/start.sh /usr/local/bin/start.sh

# Create nginx configuration
RUN echo 'user nginx;' > /etc/nginx/nginx.conf \
    && echo 'worker_processes auto;' >> /etc/nginx/nginx.conf \
    && echo 'error_log /var/log/nginx/error.log warn;' >> /etc/nginx/nginx.conf \
    && echo 'pid /var/run/nginx.pid;' >> /etc/nginx/nginx.conf \
    && echo 'events { worker_connections 1024; }' >> /etc/nginx/nginx.conf \
    && echo 'http {' >> /etc/nginx/nginx.conf \
    && echo '    include /etc/nginx/mime.types;' >> /etc/nginx/nginx.conf \
    && echo '    default_type application/octet-stream;' >> /etc/nginx/nginx.conf \
    && echo '    sendfile on;' >> /etc/nginx/nginx.conf \
    && echo '    keepalive_timeout 65;' >> /etc/nginx/nginx.conf \
    && echo '    include /etc/nginx/http.d/*.conf;' >> /etc/nginx/nginx.conf \
    && echo '}' >> /etc/nginx/nginx.conf

# Create necessary directories and set permissions
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        /var/log/supervisor \
        /var/log/nginx \
        /run/nginx \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x /usr/local/bin/start.sh

# Create storage link
RUN php artisan storage:link || true

# Set environment variables for production
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV SESSION_DRIVER=file
ENV CACHE_DRIVER=file

# Expose port (Render uses PORT environment variable)
EXPOSE ${PORT:-80}

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost:${PORT:-80}/health || exit 1

# Update nginx configuration to use PORT environment variable
RUN sed -i 's/listen 80;/listen ${PORT:-80};/' /etc/nginx/http.d/default.conf

# Create entrypoint script that handles PORT variable
RUN echo '#!/bin/sh' > /entrypoint.sh \
    && echo 'export PORT=${PORT:-80}' >> /entrypoint.sh \
    && echo 'sed -i "s/listen 80;/listen $PORT;/" /etc/nginx/http.d/default.conf' >> /entrypoint.sh \
    && echo 'exec /usr/local/bin/start.sh' >> /entrypoint.sh \
    && chmod +x /entrypoint.sh

# Start the application
CMD ["/entrypoint.sh"]

# =============================================================================
# Render Deployment Instructions:
# =============================================================================
#
# 1. Build and push to Docker Hub:
#    docker build -f Dockerfile.render -t yourusername/fintrack:latest .
#    docker push yourusername/fintrack:latest
#
# 2. In Render dashboard:
#    - Create new Web Service
#    - Connect your repository
#    - Set Docker image: yourusername/fintrack:latest
#    - Set environment variables:
#      * APP_KEY=base64:your-app-key
#      * DATABASE_URL=your-database-url
#      * APP_URL=https://your-app.onrender.com
#
# 3. Environment Variables for Render:
#    APP_NAME=Personal Financial Tracker
#    APP_ENV=production
#    APP_DEBUG=false
#    APP_URL=https://your-app.onrender.com
#    APP_KEY=base64:your-generated-key
#    DATABASE_URL=mysql://user:pass@host:port/dbname
#    CACHE_DRIVER=file
#    SESSION_DRIVER=file
#    QUEUE_CONNECTION=sync
#    MAIL_MAILER=log
#
# =============================================================================