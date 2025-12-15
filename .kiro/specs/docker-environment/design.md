# Docker Environment Design Document

## Overview

This design document outlines the architecture and implementation details for a complete Docker-based development environment for the Personal Financial Tracker Laravel application. The environment uses Docker Compose to orchestrate multiple containers (PHP-FPM, Nginx, MySQL, Redis, phpMyAdmin, and Node.js) that work together to provide a fully functional development setup. The design emphasizes ease of use, performance, and consistency across different development machines while maintaining the ability to deploy to production environments.

## Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Host Machine                          │
│                                                              │
│  ┌────────────────────────────────────────────────────┐    │
│  │           Docker Compose Network (app-network)      │    │
│  │                                                      │    │
│  │  ┌──────────┐    ┌──────────┐    ┌──────────┐     │    │
│  │  │  Nginx   │───▶│ PHP-FPM  │───▶│  MySQL   │     │    │
│  │  │  :80     │    │  :9000   │    │  :3306   │     │    │
│  │  └──────────┘    └──────────┘    └──────────┘     │    │
│  │       │               │                 │          │    │
│  │       │               │                 │          │    │
│  │       │               ▼                 ▼          │    │
│  │       │          ┌──────────┐    ┌──────────┐     │    │
│  │       │          │  Redis   │    │phpMyAdmin│     │    │
│  │       │          │  :6379   │    │  :8080   │     │    │
│  │       │          └──────────┘    └──────────┘     │    │
│  │       │                                            │    │
│  │       ▼                                            │    │
│  │  ┌──────────┐                                      │    │
│  │  │ Node.js  │                                      │    │
│  │  │  :5173   │  (Vite Dev Server)                   │    │
│  │  └──────────┘                                      │    │
│  │                                                      │    │
│  └────────────────────────────────────────────────────┘    │
│                                                              │
│  Volumes:                                                    │
│  • mysql_data (persistent)                                   │
│  • redis_data (persistent)                                   │
│  • ./app → /var/www/html (bind mount)                       │
│                                                              │
└─────────────────────────────────────────────────────────────┘
```

### Container Communication Flow

1. **HTTP Request Flow:**
   - Browser → Nginx (port 80)
   - Nginx → PHP-FPM (port 9000) for PHP files
   - PHP-FPM → MySQL (port 3306) for database queries
   - PHP-FPM → Redis (port 6379) for cache/sessions

2. **Asset Development Flow:**
   - Developer edits files → Bind mount syncs to containers
   - Node.js watches files → Vite compiles assets
   - Vite HMR → Browser (port 5173)

3. **Database Management Flow:**
   - Browser → phpMyAdmin (port 8080)
   - phpMyAdmin → MySQL (port 3306)

## Components and Interfaces

### 1. PHP-FPM Container

**Base Image:** `php:8.2-fpm-alpine`

**Dockerfile Structure:**
```dockerfile
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
```

**Configuration Files:**
- `docker/php/php.ini` - Custom PHP settings
- `docker/php/www.conf` - PHP-FPM pool configuration

**Key Settings:**
```ini
memory_limit = 512M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
```

**Exposed Ports:** 9000 (internal only)

**Volumes:**
- Application code: `./:/var/www/html`
- Composer cache: `composer_cache:/root/.composer`

### 2. Nginx Container

**Base Image:** `nginx:alpine`

**Configuration Structure:**
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Configuration Files:**
- `docker/nginx/default.conf` - Main server configuration
- `docker/nginx/nginx.conf` - Global Nginx settings

**Exposed Ports:** 80 (mapped to host)

**Volumes:**
- Application code: `./:/var/www/html:ro` (read-only)
- Nginx config: `./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf`

### 3. MySQL Container

**Base Image:** `mysql:8.0`

**Environment Variables:**
```env
MYSQL_ROOT_PASSWORD=root_password
MYSQL_DATABASE=financial_tracker
MYSQL_USER=laravel_user
MYSQL_PASSWORD=laravel_password
```

**Configuration Files:**
- `docker/mysql/my.cnf` - Custom MySQL configuration

**Key Settings:**
```ini
[mysqld]
character-set-server=utf8mb4
collation-server=utf8mb4_unicode_ci
max_connections=200
innodb_buffer_pool_size=256M
```

**Exposed Ports:** 3306 (internal and optionally mapped to host)

**Volumes:**
- Data persistence: `mysql_data:/var/lib/mysql`
- Custom config: `./docker/mysql/my.cnf:/etc/mysql/conf.d/custom.cnf`

**Health Check:**
```yaml
healthcheck:
  test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
  interval: 10s
  timeout: 5s
  retries: 5
```

### 4. Redis Container

**Base Image:** `redis:alpine`

**Configuration Files:**
- `docker/redis/redis.conf` - Custom Redis configuration

**Key Settings:**
```conf
maxmemory 256mb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

**Exposed Ports:** 6379 (internal only)

**Volumes:**
- Data persistence: `redis_data:/data`
- Custom config: `./docker/redis/redis.conf:/usr/local/etc/redis/redis.conf`

**Health Check:**
```yaml
healthcheck:
  test: ["CMD", "redis-cli", "ping"]
  interval: 10s
  timeout: 3s
  retries: 5
```

### 5. phpMyAdmin Container

**Base Image:** `phpmyadmin:latest`

**Environment Variables:**
```env
PMA_HOST=mysql
PMA_PORT=3306
PMA_USER=root
PMA_PASSWORD=root_password
UPLOAD_LIMIT=20M
```

**Exposed Ports:** 8080 (mapped to host)

**Dependencies:** Requires MySQL container to be healthy

### 6. Node.js Container

**Base Image:** `node:20-alpine`

**Dockerfile Structure:**
```dockerfile
FROM node:20-alpine

WORKDIR /var/www/html

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy application files
COPY . .

# Expose Vite dev server port
EXPOSE 5173

# Default command
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
```

**Exposed Ports:** 5173 (mapped to host for HMR)

**Volumes:**
- Application code: `./:/var/www/html`
- Node modules: `node_modules:/var/www/html/node_modules`

**Environment Variables:**
```env
VITE_HOST=0.0.0.0
VITE_PORT=5173
```

## Data Models

### Docker Compose Configuration

**File:** `docker-compose.yml`

```yaml
version: '3.8'

services:
  # PHP-FPM Service
  php:
    build:
      context: .
      dockerfile: docker/php/Dockerfile
    container_name: fintrack_php
    restart: unless-stopped
    working_dir: /var/www/html
    volumes:
      - ./:/var/www/html
      - ./docker/php/php.ini:/usr/local/etc/php/conf.d/custom.ini
      - composer_cache:/root/.composer
    networks:
      - app-network
    depends_on:
      mysql:
        condition: service_healthy
      redis:
        condition: service_healthy

  # Nginx Service
  nginx:
    image: nginx:alpine
    container_name: fintrack_nginx
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - ./:/var/www/html:ro
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
    networks:
      - app-network
    depends_on:
      - php

  # MySQL Service
  mysql:
    image: mysql:8.0
    container_name: fintrack_mysql
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-root}
      MYSQL_DATABASE: ${DB_DATABASE:-financial_tracker}
      MYSQL_USER: ${DB_USERNAME:-laravel}
      MYSQL_PASSWORD: ${DB_PASSWORD:-secret}
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/custom.cnf
    networks:
      - app-network
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      timeout: 5s
      retries: 5

  # Redis Service
  redis:
    image: redis:alpine
    container_name: fintrack_redis
    restart: unless-stopped
    ports:
      - "6379:6379"
    volumes:
      - redis_data:/data
      - ./docker/redis/redis.conf:/usr/local/etc/redis/redis.conf
    command: redis-server /usr/local/etc/redis/redis.conf
    networks:
      - app-network
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 3s
      retries: 5

  # phpMyAdmin Service
  phpmyadmin:
    image: phpmyadmin:latest
    container_name: fintrack_phpmyadmin
    restart: unless-stopped
    environment:
      PMA_HOST: mysql
      PMA_PORT: 3306
      PMA_USER: root
      PMA_PASSWORD: ${DB_ROOT_PASSWORD:-root}
      UPLOAD_LIMIT: 20M
    ports:
      - "8080:80"
    networks:
      - app-network
    depends_on:
      mysql:
        condition: service_healthy

  # Node.js Service
  node:
    build:
      context: .
      dockerfile: docker/node/Dockerfile
    container_name: fintrack_node
    restart: unless-stopped
    working_dir: /var/www/html
    ports:
      - "5173:5173"
    volumes:
      - ./:/var/www/html
      - node_modules:/var/www/html/node_modules
    networks:
      - app-network
    command: npm run dev -- --host 0.0.0.0

networks:
  app-network:
    driver: bridge

volumes:
  mysql_data:
    driver: local
  redis_data:
    driver: local
  composer_cache:
    driver: local
  node_modules:
    driver: local
```

### Environment Configuration

**File:** `.env.docker`

```env
# Application
APP_NAME="Financial Tracker"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=financial_tracker
DB_USERNAME=laravel
DB_PASSWORD=secret
DB_ROOT_PASSWORD=root

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (for development)
MAIL_MAILER=log
MAIL_HOST=mailhog
MAIL_PORT=1025

# Vite
VITE_APP_NAME="${APP_NAME}"
```

## Error Handling

### Container Startup Failures

**Problem:** Container fails to start due to port conflicts

**Solution:**
- Check for processes using required ports: `netstat -ano | findstr :80`
- Modify port mappings in docker-compose.yml
- Use alternative ports (e.g., 8000:80 instead of 80:80)

**Problem:** MySQL container fails health check

**Solution:**
- Increase health check timeout and retries
- Check MySQL logs: `docker-compose logs mysql`
- Verify environment variables are set correctly
- Ensure sufficient disk space for MySQL data

### Permission Issues

**Problem:** Laravel cannot write to storage or cache directories

**Solution:**
- Run permission fix command in PHP container:
  ```bash
  docker-compose exec php chown -R www-data:www-data storage bootstrap/cache
  docker-compose exec php chmod -R 775 storage bootstrap/cache
  ```

**Problem:** Composer or npm cannot install dependencies

**Solution:**
- Use volume mounts for vendor and node_modules
- Run installations inside containers with proper user permissions
- Clear caches if needed

### Network Connectivity Issues

**Problem:** PHP cannot connect to MySQL or Redis

**Solution:**
- Verify all services are on the same Docker network
- Use service names (mysql, redis) instead of localhost
- Check service health status: `docker-compose ps`
- Ensure depends_on with health checks are configured

### Asset Compilation Issues

**Problem:** Vite dev server not accessible or HMR not working

**Solution:**
- Ensure Vite is configured to listen on 0.0.0.0
- Check port 5173 is properly mapped
- Update vite.config.js:
  ```js
  server: {
    host: '0.0.0.0',
    port: 5173,
    hmr: {
      host: 'localhost'
    }
  }
  ```

## Testing Strategy

### Container Health Verification

**Test 1: Service Availability**
```bash
# Check all containers are running
docker-compose ps

# Expected: All services show "Up" status
```

**Test 2: Network Connectivity**
```bash
# Test PHP to MySQL connection
docker-compose exec php php artisan migrate:status

# Test PHP to Redis connection
docker-compose exec php php artisan tinker
>>> Redis::ping()
```

**Test 3: Web Server Response**
```bash
# Test Nginx is serving the application
curl http://localhost

# Expected: HTML response from Laravel
```

### Application Functionality Tests

**Test 4: Database Operations**
```bash
# Run migrations
docker-compose exec php php artisan migrate

# Run seeders
docker-compose exec php php artisan db:seed

# Expected: No errors, database populated
```

**Test 5: Asset Compilation**
```bash
# Build assets
docker-compose exec node npm run build

# Expected: Compiled assets in public/build directory
```

**Test 6: Cache Operations**
```bash
# Test cache
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan config:cache

# Expected: Cache cleared and recached successfully
```

### Performance Tests

**Test 7: Response Time**
```bash
# Measure application response time
curl -w "@curl-format.txt" -o /dev/null -s http://localhost

# Expected: Response time < 500ms for simple pages
```

**Test 8: Concurrent Requests**
```bash
# Use Apache Bench or similar tool
ab -n 100 -c 10 http://localhost/

# Expected: All requests successful, reasonable response times
```

### Integration Tests

**Test 9: Full Stack Test**
1. Start all containers: `docker-compose up -d`
2. Run migrations: `docker-compose exec php php artisan migrate:fresh --seed`
3. Access application: http://localhost
4. Access phpMyAdmin: http://localhost:8080
5. Check Vite HMR: http://localhost:5173
6. Run PHPUnit tests: `docker-compose exec php php artisan test`

**Expected Results:**
- All services accessible
- Application loads correctly
- Database contains seeded data
- All tests pass

### Cleanup and Reset Tests

**Test 10: Environment Reset**
```bash
# Stop and remove all containers
docker-compose down

# Remove volumes
docker-compose down -v

# Rebuild and restart
docker-compose up -d --build

# Expected: Clean environment, all services start successfully
```

## Production Considerations

### Production Docker Compose

**File:** `docker-compose.prod.yml`

Key differences from development:
- Multi-stage builds for smaller images
- No bind mounts (code copied into images)
- Optimized PHP-FPM and Nginx configurations
- No phpMyAdmin (security)
- No Vite dev server (pre-built assets)
- Health checks for all services
- Resource limits defined
- Secrets management for sensitive data
- Logging configuration

### Security Hardening

1. **Non-root users:** Run containers as non-root users
2. **Read-only filesystems:** Mount application code as read-only where possible
3. **Network isolation:** Use separate networks for frontend and backend
4. **Secret management:** Use Docker secrets or environment variable injection
5. **Image scanning:** Scan images for vulnerabilities before deployment
6. **Minimal base images:** Use Alpine Linux for smaller attack surface

### Performance Optimization

1. **PHP-FPM tuning:**
   - Adjust pm.max_children based on available memory
   - Use pm = dynamic for production
   - Enable opcache with proper settings

2. **MySQL optimization:**
   - Increase innodb_buffer_pool_size
   - Configure query cache
   - Set appropriate max_connections

3. **Redis optimization:**
   - Configure maxmemory and eviction policy
   - Enable persistence if needed
   - Use Redis for sessions and cache

4. **Nginx optimization:**
   - Enable gzip compression
   - Configure browser caching
   - Use FastCGI caching for PHP

### Monitoring and Logging

1. **Container logs:** Centralized logging with ELK stack or similar
2. **Health checks:** Regular health check endpoints
3. **Metrics:** Prometheus exporters for each service
4. **Alerts:** Configure alerts for service failures
5. **APM:** Application Performance Monitoring integration

## Helper Scripts and Documentation

### Makefile Commands

**File:** `Makefile`

```makefile
.PHONY: help build up down restart logs shell composer npm artisan test migrate seed fresh

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Build all containers
	docker-compose build

up: ## Start all containers
	docker-compose up -d

down: ## Stop all containers
	docker-compose down

restart: ## Restart all containers
	docker-compose restart

logs: ## Show logs from all containers
	docker-compose logs -f

shell: ## Access PHP container shell
	docker-compose exec php sh

composer: ## Run composer install
	docker-compose exec php composer install

npm: ## Run npm install
	docker-compose exec node npm install

artisan: ## Run artisan command (usage: make artisan cmd="migrate")
	docker-compose exec php php artisan $(cmd)

test: ## Run PHPUnit tests
	docker-compose exec php php artisan test

migrate: ## Run database migrations
	docker-compose exec php php artisan migrate

seed: ## Run database seeders
	docker-compose exec php php artisan db:seed

fresh: ## Fresh database with seeders
	docker-compose exec php php artisan migrate:fresh --seed

setup: ## Initial setup (build, up, install dependencies, migrate)
	make build
	make up
	make composer
	make npm
	docker-compose exec php php artisan key:generate
	make migrate
	make seed
```

### Documentation Files

**File:** `README.docker.md`

Contents:
- Prerequisites (Docker, Docker Compose)
- Quick start guide
- Available services and ports
- Common commands
- Troubleshooting guide
- FAQ section
- Links to additional resources

**File:** `.env.docker.example`

Contents:
- All required environment variables with descriptions
- Sensible defaults for development
- Comments explaining each variable
- Security notes for production

This design provides a complete, production-ready Docker environment that's easy to use for development and can be adapted for production deployment.
