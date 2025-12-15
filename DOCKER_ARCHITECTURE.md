# Docker Architecture Documentation

## Table of Contents

- [Overview](#overview)
- [Architecture Diagram](#architecture-diagram)
- [Container Services](#container-services)
- [Network Architecture](#network-architecture)
- [Volume Management](#volume-management)
- [Configuration Files](#configuration-files)
- [Service Communication](#service-communication)
- [Development vs Production](#development-vs-production)
- [Performance Considerations](#performance-considerations)
- [Security Architecture](#security-architecture)
- [Monitoring and Logging](#monitoring-and-logging)
- [Scaling Considerations](#scaling-considerations)

## Overview

The Personal Financial Tracker application uses a multi-container Docker architecture orchestrated by Docker Compose. This architecture provides:

- **Isolation**: Each service runs in its own container with dedicated resources
- **Portability**: Consistent environment across development, staging, and production
- **Scalability**: Easy to scale individual services independently
- **Maintainability**: Clear separation of concerns and standardized configuration
- **Reproducibility**: Identical setup for all developers

### Technology Stack

- **Application Framework**: Laravel 11.x (PHP 8.2)
- **Web Server**: Nginx (Alpine Linux)
- **Database**: MySQL 8.0
- **Cache/Session Store**: Redis (Alpine Linux)
- **Asset Compilation**: Node.js 20 LTS with Vite
- **Database Management**: phpMyAdmin (development only)

## Architecture Diagram

### High-Level System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                           Host Machine                               │
│                                                                       │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │              Docker Network (app-network)                    │   │
│  │                                                               │   │
│  │  ┌──────────────┐                                            │   │
│  │  │   Browser    │                                            │   │
│  │  └──────┬───────┘                                            │   │
│  │         │                                                     │   │
│  │         ├──────────────────────────────────────────────────┐  │   │
│  │         │                                                   │  │   │
│  │         ▼ :80                        ▼ :8080        ▼ :5173│  │   │
│  │  ┌─────────────┐              ┌──────────────┐  ┌─────────┴┐ │   │
│  │  │   Nginx     │              │ phpMyAdmin   │  │  Node.js  │ │   │
│  │  │  (Alpine)   │              │  (Latest)    │  │ (20-Alpine)│ │   │
│  │  │             │              │              │  │           │ │   │
│  │  │ Port: 80    │              │ Port: 8080   │  │ Port: 5173│ │   │
│  │  └──────┬──────┘              └──────┬───────┘  └───────────┘ │   │
│  │         │                            │                         │   │
│  │         │ FastCGI                    │                         │   │
│  │         │ :9000                      │                         │   │
│  │         ▼                            │                         │   │
│  │  ┌─────────────┐                    │                         │   │
│  │  │  PHP-FPM    │                    │                         │   │
│  │  │ (8.2-Alpine)│                    │                         │   │
│  │  │             │                    │                         │   │
│  │  │ Port: 9000  │                    │                         │   │
│  │  └──────┬──────┘                    │                         │   │
│  │         │                            │                         │   │
│  │         ├────────────────────────────┘                         │   │
│  │         │                                                      │   │
│  │         ├──────────────┬──────────────┐                       │   │
│  │         │              │              │                       │   │
│  │         ▼ :3306        ▼ :6379        │                       │   │
│  │  ┌─────────────┐  ┌─────────────┐    │                       │   │
│  │  │   MySQL     │  │   Redis     │    │                       │   │
│  │  │   (8.0)     │  │  (Alpine)   │    │                       │   │
│  │  │             │  │             │    │                       │   │
│  │  │ Port: 3306  │  │ Port: 6379  │    │                       │   │
│  │  └──────┬──────┘  └──────┬──────┘    │                       │   │
│  │         │                 │           │                       │   │
│  │         ▼                 ▼           ▼                       │   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐          │   │
│  │  │mysql_data   │  │redis_data   │  │composer_    │          │   │
│  │  │  (Volume)   │  │  (Volume)   │  │cache(Volume)│          │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘          │   │
│  │                                                               │   │
│  └───────────────────────────────────────────────────────────────┘   │
│                                                                       │
│  Application Code: ./  →  /var/www/html (Bind Mount)                │
│                                                                       │
└───────────────────────────────────────────────────────────────────────┘
```

### Request Flow Diagram

```
┌──────────┐
│ Browser  │
└────┬─────┘
     │ HTTP Request
     ▼
┌─────────────────┐
│  Nginx :80      │  1. Receives HTTP request
│                 │  2. Checks if static file
│  Static? ───Yes─┼──→ Serve directly
│     │           │
│     No          │
│     ▼           │
│  Forward to     │  3. Proxy to PHP-FPM via FastCGI
│  PHP-FPM :9000  │
└────┬────────────┘
     │
     ▼
┌─────────────────┐
│ PHP-FPM :9000   │  4. Execute PHP code (Laravel)
│                 │  5. Process request through Laravel
└────┬────────────┘
     │
     ├──────────────────┬──────────────────┐
     │                  │                  │
     ▼                  ▼                  ▼
┌──────────┐    ┌──────────┐      ┌──────────┐
│MySQL:3306│    │Redis:6379│      │Filesystem│
│          │    │          │      │          │
│Database  │    │Cache/    │      │Storage/  │
│Queries   │    │Sessions  │      │Uploads   │
└──────────┘    └──────────┘      └──────────┘
     │                  │                  │
     └──────────────────┴──────────────────┘
                        │
                        ▼
                  ┌──────────┐
                  │ Response │
                  └────┬─────┘
                       │
                       ▼
                  ┌──────────┐
                  │ Browser  │
                  └──────────┘
```

### Asset Development Flow (Vite HMR)

```
┌──────────┐
│Developer │
│Edits File│
└────┬─────┘
     │
     ▼
┌─────────────────┐
│ Bind Mount      │  File change detected
│ ./resources →   │
│ /var/www/html   │
└────┬────────────┘
     │
     ▼
┌─────────────────┐
│ Node.js :5173   │  1. Vite watches for changes
│                 │  2. Recompiles assets
│ Vite Dev Server │  3. Sends HMR update
└────┬────────────┘
     │ WebSocket
     ▼
┌─────────────────┐
│ Browser :5173   │  4. Hot Module Replacement
│                 │  5. Updates without page reload
│ Live Reload     │
└─────────────────┘
```

## Container Services

### 1. PHP-FPM Container (php)

**Purpose**: Executes PHP code and runs the Laravel application.

**Configuration**:
- **Base Image**: `php:8.2-fpm-alpine`
- **Container Name**: `fintrack_php`
- **Working Directory**: `/var/www/html`
- **Restart Policy**: `unless-stopped`

**Installed Extensions**:
```
- pdo_mysql      (Database connectivity)
- mbstring       (Multibyte string support)
- exif           (Image metadata)
- pcntl          (Process control)
- bcmath         (Arbitrary precision mathematics)
- gd             (Image processing)
- zip            (Archive handling)
```

**System Dependencies**:
```
- git            (Version control)
- curl           (HTTP requests)
- libpng-dev     (PNG image support)
- libzip-dev     (ZIP archive support)
- zip/unzip      (Archive utilities)
```

**Volumes**:
```yaml
- ./:/var/www/html                                    # Application code
- ./docker/php/php.ini:/usr/local/etc/php/conf.d/custom.ini  # PHP config
- ./docker/php/www.conf:/usr/local/etc/php-fpm.d/www.conf    # FPM config
- composer_cache:/root/.composer                      # Composer cache
```

**PHP Configuration** (`php.ini`):
```ini
memory_limit = 512M
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
opcache.enable = 1
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 10000
opcache.revalidate_freq = 2
```

**PHP-FPM Configuration** (`www.conf`):
```ini
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

**Dependencies**:
- MySQL (with health check)
- Redis (with health check)

### 2. Nginx Container (nginx)

**Purpose**: Web server that handles HTTP requests and serves static files.

**Configuration**:
- **Base Image**: `nginx:alpine`
- **Container Name**: `fintrack_nginx`
- **Exposed Ports**: `80:80`
- **Restart Policy**: `unless-stopped`

**Volumes**:
```yaml
- ./:/var/www/html:ro                                 # Application code (read-only)
- ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro  # Server config
- ./docker/nginx/nginx.conf:/etc/nginx/nginx.conf:ro  # Global config
```

**Server Configuration** (`default.conf`):
```nginx
server {
    listen 80;
    server_name localhost;
    root /var/www/html/public;
    index index.php index.html;

    # Laravel routing
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Security: deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Global Configuration** (`nginx.conf`):
```nginx
worker_processes auto;
events {
    worker_connections 1024;
}
http {
    include mime.types;
    default_type application/octet-stream;
    sendfile on;
    keepalive_timeout 65;
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;
}
```

**Dependencies**:
- PHP-FPM container

### 3. MySQL Container (mysql)

**Purpose**: Relational database for persistent data storage.

**Configuration**:
- **Base Image**: `mysql:8.0`
- **Container Name**: `fintrack_mysql`
- **Exposed Ports**: `3306:3306`
- **Restart Policy**: `unless-stopped`

**Environment Variables**:
```yaml
MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-root}
MYSQL_DATABASE: ${DB_DATABASE:-financial_tracker}
```

**Volumes**:
```yaml
- mysql_data:/var/lib/mysql                           # Data persistence
- ./docker/mysql/my.cnf:/etc/mysql/conf.d/custom.cnf:ro  # Custom config
- ./docker/mysql/init.sql:/docker-entrypoint-initdb.d/init.sql:ro  # Init script
```

**Custom Configuration** (`my.cnf`):
```ini
[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
max_connections = 200
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT
```

**Health Check**:
```yaml
test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-u", "root", "-p${DB_ROOT_PASSWORD:-root}"]
interval: 10s
timeout: 5s
retries: 5
start_period: 30s
```

**Data Persistence**:
- All database data stored in `mysql_data` named volume
- Survives container restarts and rebuilds
- Can be backed up using `docker volume` commands

### 4. Redis Container (redis)

**Purpose**: In-memory data store for caching and session management.

**Configuration**:
- **Base Image**: `redis:alpine`
- **Container Name**: `fintrack_redis`
- **Exposed Ports**: `6379:6379`
- **Restart Policy**: `unless-stopped`

**Volumes**:
```yaml
- redis_data:/data                                    # Data persistence
- ./docker/redis/redis.conf:/usr/local/etc/redis/redis.conf:ro  # Custom config
```

**Custom Configuration** (`redis.conf`):
```conf
# Memory management
maxmemory 256mb
maxmemory-policy allkeys-lru

# Persistence (RDB snapshots)
save 900 1      # Save after 900 seconds if at least 1 key changed
save 300 10     # Save after 300 seconds if at least 10 keys changed
save 60 10000   # Save after 60 seconds if at least 10000 keys changed

# AOF persistence (optional)
appendonly no

# Networking
bind 0.0.0.0
protected-mode no
```

**Health Check**:
```yaml
test: ["CMD", "redis-cli", "ping"]
interval: 10s
timeout: 3s
retries: 5
start_period: 10s
```

**Use Cases**:
- Session storage (SESSION_DRIVER=redis)
- Application cache (CACHE_DRIVER=redis)
- Queue backend (QUEUE_CONNECTION=redis)

### 5. phpMyAdmin Container (phpmyadmin)

**Purpose**: Web-based database management interface (development only).

**Configuration**:
- **Base Image**: `phpmyadmin:latest`
- **Container Name**: `fintrack_phpmyadmin`
- **Exposed Ports**: `8080:80`
- **Restart Policy**: `unless-stopped`

**Environment Variables**:
```yaml
PMA_HOST: mysql
PMA_PORT: 3306
PMA_USER: root
PMA_PASSWORD: ${DB_ROOT_PASSWORD:-root}
UPLOAD_LIMIT: 20M
```

**Dependencies**:
- MySQL (with health check)

**Security Note**: This service should be removed in production environments.

### 6. Node.js Container (node)

**Purpose**: Asset compilation and Vite development server.

**Configuration**:
- **Base Image**: `node:20-alpine`
- **Container Name**: `fintrack_node`
- **Exposed Ports**: `5173:5173`
- **Restart Policy**: `unless-stopped`

**Volumes**:
```yaml
- ./:/var/www/html                                    # Application code
- node_modules:/var/www/html/node_modules             # Dependencies
```

**Command**:
```bash
npm run dev -- --host 0.0.0.0
```

**Vite Configuration** (`vite.config.js`):
```javascript
export default defineConfig({
    server: {
        host: '0.0.0.0',      // Listen on all interfaces
        port: 5173,           // Vite dev server port
        hmr: {
            host: 'localhost'  // HMR host for browser
        },
        watch: {
            usePolling: true   // Required for Docker file watching
        }
    }
});
```

**Features**:
- Hot Module Replacement (HMR)
- Automatic asset recompilation
- Live browser reload

## Network Architecture

### Docker Network Configuration

**Network Name**: `app-network`
**Driver**: `bridge`

**Network Topology**:
```
app-network (172.18.0.0/16)
├── nginx       (172.18.0.2)
├── php         (172.18.0.3)
├── mysql       (172.18.0.4)
├── redis       (172.18.0.5)
├── phpmyadmin  (172.18.0.6)
└── node        (172.18.0.7)
```

### Service Discovery

Docker Compose provides automatic DNS resolution:
- Services can communicate using service names as hostnames
- Example: PHP connects to MySQL using `DB_HOST=mysql`
- No need for IP addresses or manual DNS configuration

### Port Mapping

**External (Host) → Internal (Container)**:
```
80    → nginx:80       (HTTP traffic)
8080  → phpmyadmin:80  (Database management)
5173  → node:5173      (Vite HMR)
3306  → mysql:3306     (Database - optional)
6379  → redis:6379     (Cache - optional)
```

**Internal Only** (not exposed to host):
```
php:9000  (FastCGI - only accessible from nginx)
```

### Network Security

**Development**:
- All ports exposed for easy access
- No authentication on Redis
- phpMyAdmin accessible

**Production**:
- Only port 80/443 exposed
- Redis password protected
- phpMyAdmin removed
- Database not exposed externally

## Volume Management

### Named Volumes

**1. mysql_data**
- **Purpose**: Persistent MySQL database storage
- **Location**: Docker managed volume
- **Size**: Grows with database size
- **Backup**: Use `mysqldump` or volume backup

**2. redis_data**
- **Purpose**: Persistent Redis data (if persistence enabled)
- **Location**: Docker managed volume
- **Size**: Limited by maxmemory setting (256MB)
- **Backup**: Use `redis-cli SAVE` or volume backup

**3. composer_cache**
- **Purpose**: Cache Composer packages for faster installs
- **Location**: Docker managed volume
- **Size**: Varies (typically 100-500MB)
- **Backup**: Not necessary (can be regenerated)

**4. node_modules**
- **Purpose**: Node.js dependencies
- **Location**: Docker managed volume
- **Size**: Varies (typically 200-500MB)
- **Backup**: Not necessary (can be regenerated)

### Bind Mounts

**Application Code** (`./:/var/www/html`):
- **Purpose**: Sync code between host and containers
- **Direction**: Bidirectional
- **Performance**: Can be slow on Windows/macOS
- **Use Case**: Development (live code updates)

**Configuration Files**:
- PHP config: `./docker/php/php.ini`
- Nginx config: `./docker/nginx/default.conf`
- MySQL config: `./docker/mysql/my.cnf`
- Redis config: `./docker/redis/redis.conf`

### Volume Management Commands

```bash
# List all volumes
docker volume ls

# Inspect a volume
docker volume inspect fintrack_mysql_data

# Backup a volume
docker run --rm -v fintrack_mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql_backup.tar.gz /data

# Restore a volume
docker run --rm -v fintrack_mysql_data:/data -v $(pwd):/backup alpine tar xzf /backup/mysql_backup.tar.gz -C /

# Remove all volumes (WARNING: deletes data)
docker-compose down -v
```

## Configuration Files

### Docker Compose Files

**1. docker-compose.yml** (Development)
- All services including phpMyAdmin and Vite
- Bind mounts for live code updates
- Exposed ports for debugging
- No resource limits
- Development-optimized settings

**2. docker-compose.prod.yml** (Production)
- Production services only (no phpMyAdmin, no Vite)
- Code copied into images (no bind mounts)
- Resource limits defined
- Health checks for all services
- Logging configuration
- Security hardening

### Environment Files

**1. .env.docker.example** (Development Template)
- All required environment variables
- Development-friendly defaults
- Detailed comments and documentation
- Service names for Docker networking

**2. .env.production.example** (Production Template)
- Production-safe defaults
- Security notes and warnings
- Strong password placeholders
- HTTPS configuration

### Dockerfile Locations

```
docker/
├── php/
│   ├── Dockerfile          # Development PHP image
│   ├── Dockerfile.prod     # Production PHP image
│   ├── php.ini             # PHP configuration
│   └── www.conf            # PHP-FPM pool configuration
├── nginx/
│   ├── default.conf        # Development Nginx config
│   ├── default.prod.conf   # Production Nginx config
│   └── nginx.conf          # Global Nginx settings
├── mysql/
│   ├── my.cnf              # MySQL configuration
│   └── init.sql            # Database initialization
├── redis/
│   └── redis.conf          # Redis configuration
└── node/
    └── Dockerfile          # Node.js image
```

## Service Communication

### PHP to MySQL

**Connection Method**: TCP/IP via Docker network

**Configuration** (`.env.docker`):
```env
DB_CONNECTION=mysql
DB_HOST=mysql          # Docker service name
DB_PORT=3306
DB_DATABASE=financial_tracker
DB_USERNAME=laravel
DB_PASSWORD=secret
```

**Laravel Database Configuration** (`config/database.php`):
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
],
```

**Connection Flow**:
1. Laravel reads DB_HOST=mysql from environment
2. Docker DNS resolves "mysql" to MySQL container IP
3. PHP PDO establishes TCP connection on port 3306
4. MySQL authenticates using provided credentials

### PHP to Redis

**Connection Method**: TCP/IP via Docker network

**Configuration** (`.env.docker`):
```env
REDIS_HOST=redis       # Docker service name
REDIS_PORT=6379
REDIS_PASSWORD=null
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

**Laravel Redis Configuration** (`config/database.php`):
```php
'redis' => [
    'client' => env('REDIS_CLIENT', 'phpredis'),
    'default' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => 0,
    ],
],
```

**Use Cases**:
- **Cache**: `Cache::put('key', 'value', 3600)`
- **Session**: Automatic session storage
- **Queue**: `dispatch(new Job())`

### Nginx to PHP-FPM

**Connection Method**: FastCGI protocol

**Nginx Configuration**:
```nginx
location ~ \.php$ {
    fastcgi_pass php:9000;                    # Service name and port
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

**Communication Flow**:
1. Nginx receives HTTP request for .php file
2. Nginx forwards request to PHP-FPM via FastCGI protocol
3. PHP-FPM executes PHP script
4. PHP-FPM returns response to Nginx
5. Nginx sends HTTP response to client

**FastCGI Parameters**:
```
SCRIPT_FILENAME: Full path to PHP file
REQUEST_METHOD: GET, POST, etc.
QUERY_STRING: URL parameters
CONTENT_TYPE: Request content type
CONTENT_LENGTH: Request body length
```

### Browser to Vite (HMR)

**Connection Method**: WebSocket

**Vite Configuration**:
```javascript
server: {
    host: '0.0.0.0',      // Listen on all interfaces
    port: 5173,
    hmr: {
        host: 'localhost'  // Browser connects to localhost:5173
    }
}
```

**HMR Flow**:
1. Developer edits file in `resources/js` or `resources/css`
2. File change detected via bind mount
3. Vite recompiles affected modules
4. Vite sends update via WebSocket to browser
5. Browser applies changes without full page reload

## Development vs Production

### Development Configuration

**Characteristics**:
- All services running (including phpMyAdmin, Vite)
- Bind mounts for live code updates
- Debug mode enabled (APP_DEBUG=true)
- All ports exposed for easy access
- No resource limits
- Verbose logging

**Services**:
```yaml
services:
  - php (with Xdebug optional)
  - nginx
  - mysql (exposed on 3306)
  - redis (exposed on 6379)
  - phpmyadmin (port 8080)
  - node (Vite dev server on 5173)
```

**Advantages**:
- Fast development cycle
- Easy debugging
- Live code updates
- Database management UI
- Hot module replacement

**Disadvantages**:
- Not production-ready
- Security vulnerabilities
- Performance not optimized
- Larger resource usage

### Production Configuration

**Characteristics**:
- Minimal services (no dev tools)
- Code copied into images (immutable)
- Debug mode disabled (APP_DEBUG=false)
- Only necessary ports exposed
- Resource limits defined
- Error-level logging only

**Services**:
```yaml
services:
  - php (optimized, no Xdebug)
  - nginx (with caching)
  - mysql (not exposed externally)
  - redis (password protected, not exposed)
```

**Optimizations**:
- Multi-stage Docker builds
- OPcache enabled
- Composer autoloader optimized
- Assets pre-compiled
- FastCGI caching
- Gzip compression

**Security Enhancements**:
- Non-root users
- Read-only filesystems
- Secrets management
- Network isolation
- Regular security updates

**Deployment Command**:
```bash
# Build production images
docker-compose -f docker-compose.prod.yml build

# Start production services
docker-compose -f docker-compose.prod.yml up -d

# Run optimizations
docker-compose -f docker-compose.prod.yml exec php php artisan config:cache
docker-compose -f docker-compose.prod.yml exec php php artisan route:cache
docker-compose -f docker-compose.prod.yml exec php php artisan view:cache
```

### Configuration Comparison

| Feature | Development | Production |
|---------|-------------|------------|
| APP_DEBUG | true | false |
| APP_ENV | local | production |
| Code Mount | Bind mount | Copied into image |
| phpMyAdmin | Included | Removed |
| Vite Dev Server | Running | Pre-built assets |
| MySQL Port | Exposed (3306) | Internal only |
| Redis Port | Exposed (6379) | Internal only |
| Resource Limits | None | Defined |
| Health Checks | Basic | Comprehensive |
| Logging | Verbose | Error-level |
| OPcache | Optional | Enabled |
| Xdebug | Optional | Disabled |

## Performance Considerations

### PHP-FPM Tuning

**Process Manager Settings**:
```ini
pm = dynamic                    # Dynamic process management
pm.max_children = 50            # Maximum child processes
pm.start_servers = 10           # Processes started on boot
pm.min_spare_servers = 5        # Minimum idle processes
pm.max_spare_servers = 20       # Maximum idle processes
pm.max_requests = 500           # Requests before process restart
```

**Memory Calculation**:
```
Total Memory = pm.max_children × Average Process Memory
Example: 50 × 50MB = 2.5GB maximum
```

**OPcache Configuration**:
```ini
opcache.enable = 1
opcache.memory_consumption = 128        # MB for OPcache
opcache.interned_strings_buffer = 8     # MB for strings
opcache.max_accelerated_files = 10000   # Number of files
opcache.revalidate_freq = 2             # Seconds between checks
opcache.validate_timestamps = 0         # Disable in production
```

### MySQL Optimization

**Buffer Pool**:
```ini
innodb_buffer_pool_size = 256M   # 70-80% of available RAM
```

**Connection Management**:
```ini
max_connections = 200            # Maximum concurrent connections
```

**Query Cache** (MySQL 5.7 and earlier):
```ini
query_cache_type = 1
query_cache_size = 64M
```

### Redis Optimization

**Memory Management**:
```conf
maxmemory 256mb                  # Maximum memory usage
maxmemory-policy allkeys-lru     # Eviction policy
```

**Persistence Trade-offs**:
- **RDB**: Periodic snapshots, less overhead
- **AOF**: Every write logged, more durable
- **None**: Fastest, no persistence

### Nginx Optimization

**Worker Configuration**:
```nginx
worker_processes auto;           # One per CPU core
worker_connections 1024;         # Connections per worker
```

**Caching**:
```nginx
# FastCGI cache
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=app:10m;
fastcgi_cache app;
fastcgi_cache_valid 200 60m;

# Static file caching
location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

**Compression**:
```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript;
gzip_min_length 1000;
```

### Volume Performance

**Bind Mount Performance** (Development):
- **Linux**: Native performance
- **macOS**: Slower due to osxfs
- **Windows**: Slower due to file system translation

**Improvements**:
- Use named volumes for dependencies (vendor, node_modules)
- Enable file system caching
- Use WSL2 on Windows
- Consider delegated/cached mount options

### Resource Limits (Production)

```yaml
deploy:
  resources:
    limits:
      cpus: '2'
      memory: 1G
    reservations:
      cpus: '0.5'
      memory: 512M
```

## Security Architecture

### Container Security

**1. Non-Root Users**

Production PHP container runs as `www-data`:
```dockerfile
USER www-data
```

**2. Read-Only Filesystems**

Mount application code as read-only:
```yaml
volumes:
  - ./:/var/www/html:ro
```

**3. Minimal Base Images**

Use Alpine Linux for smaller attack surface:
- `php:8.2-fpm-alpine` (smaller than Debian-based)
- `nginx:alpine`
- `redis:alpine`
- `node:20-alpine`

**4. Security Scanning**

Scan images for vulnerabilities:
```bash
docker scan fintrack_php
```

### Network Security

**1. Network Isolation**

Services communicate only within Docker network:
```yaml
networks:
  app-network:
    driver: bridge
```

**2. Port Exposure**

Production: Only expose necessary ports (80/443)
```yaml
ports:
  - "80:80"
  # - "443:443"  # For HTTPS
```

**3. Service Segmentation**

Separate networks for frontend and backend (advanced):
```yaml
networks:
  frontend:
    driver: bridge
  backend:
    driver: bridge
    internal: true  # No external access
```

### Application Security

**1. Environment Variables**

Never commit secrets to version control:
```bash
# Use .env.docker (gitignored)
# Or use Docker secrets
docker secret create db_password ./db_password.txt
```

**2. Redis Authentication**

Enable password in production:
```conf
requirepass your_strong_password
```

```env
REDIS_PASSWORD=your_strong_password
```

**3. MySQL Security**

- Use strong passwords
- Limit user privileges
- Don't expose port externally in production
- Regular security updates

**4. HTTPS/TLS**

Production should use HTTPS:
```nginx
server {
    listen 443 ssl http2;
    ssl_certificate /etc/nginx/ssl/cert.pem;
    ssl_certificate_key /etc/nginx/ssl/key.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
}
```

### Security Headers

Add security headers in Nginx:
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-Content-Type-Options "nosniff" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header Referrer-Policy "no-referrer-when-downgrade" always;
add_header Content-Security-Policy "default-src 'self'" always;
```

### Secrets Management

**Development**:
- Use `.env.docker` file (gitignored)
- Simple and convenient

**Production**:
- Docker Secrets
- AWS Secrets Manager
- HashiCorp Vault
- Azure Key Vault

**Example with Docker Secrets**:
```yaml
services:
  php:
    secrets:
      - db_password
      - app_key

secrets:
  db_password:
    external: true
  app_key:
    external: true
```

## Monitoring and Logging

### Container Logs

**View Logs**:
```bash
# All services
docker-compose logs -f

# Specific service
docker-compose logs -f php
docker-compose logs -f nginx

# Last 100 lines
docker-compose logs --tail=100 php
```

**Log Configuration** (Production):
```yaml
logging:
  driver: "json-file"
  options:
    max-size: "10m"
    max-file: "3"
```

### Application Logging

**Laravel Log Channels** (`config/logging.php`):
```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'slack'],
    ],
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
],
```

**Access Logs**:
- **Nginx**: `/var/log/nginx/access.log`
- **PHP-FPM**: `/var/log/php-fpm/access.log`
- **MySQL**: `/var/log/mysql/error.log`

### Health Checks

**Container Health**:
```yaml
healthcheck:
  test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
  interval: 30s
  timeout: 10s
  retries: 3
  start_period: 40s
```

**Check Health Status**:
```bash
docker-compose ps
docker inspect --format='{{.State.Health.Status}}' fintrack_mysql
```

### Monitoring Tools

**1. Docker Stats**:
```bash
docker stats
```

**2. cAdvisor** (Container Advisor):
```yaml
cadvisor:
  image: gcr.io/cadvisor/cadvisor:latest
  ports:
    - "8081:8080"
  volumes:
    - /:/rootfs:ro
    - /var/run:/var/run:ro
    - /sys:/sys:ro
    - /var/lib/docker/:/var/lib/docker:ro
```

**3. Prometheus + Grafana**:
- Collect metrics from containers
- Visualize performance data
- Set up alerts

**4. ELK Stack** (Elasticsearch, Logstash, Kibana):
- Centralized log aggregation
- Log analysis and search
- Visualization dashboards

### Application Performance Monitoring (APM)

**Options**:
- New Relic
- Datadog
- Dynatrace
- Application Insights (Azure)

**Laravel Integration**:
```bash
composer require newrelic/newrelic-php-agent
```

## Scaling Considerations

### Horizontal Scaling

**Load Balancer Architecture**:
```
                    ┌──────────────┐
                    │Load Balancer │
                    │   (Nginx)    │
                    └──────┬───────┘
                           │
        ┏━━━━━━━━━━━━━━━━━━┻━━━━━━━━━━━━━━━━━━┓
        ▼                  ▼                  ▼
┌───────────────┐  ┌───────────────┐  ┌───────────────┐
│  App Server 1 │  │  App Server 2 │  │  App Server 3 │
│  (PHP+Nginx)  │  │  (PHP+Nginx)  │  │  (PHP+Nginx)  │
└───────┬───────┘  └───────┬───────┘  └───────┬───────┘
        │                  │                  │
        └──────────────────┴──────────────────┘
                           │
        ┏━━━━━━━━━━━━━━━━━━┻━━━━━━━━━━━━━━━━━━┓
        ▼                                     ▼
┌───────────────┐                    ┌───────────────┐
│     MySQL     │                    │     Redis     │
│   (Primary)   │                    │   (Cluster)   │
└───────────────┘                    └───────────────┘
```

**Docker Compose Scaling**:
```bash
# Scale PHP service to 3 instances
docker-compose up -d --scale php=3

# Requires load balancer configuration
```

**Docker Swarm**:
```yaml
services:
  php:
    deploy:
      replicas: 3
      update_config:
        parallelism: 1
        delay: 10s
      restart_policy:
        condition: on-failure
```

**Kubernetes**:
```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: php-deployment
spec:
  replicas: 3
  selector:
    matchLabels:
      app: php
  template:
    metadata:
      labels:
        app: php
    spec:
      containers:
      - name: php
        image: fintrack_php:latest
```

### Vertical Scaling

**Increase Resources**:
```yaml
deploy:
  resources:
    limits:
      cpus: '4'      # Increase from 2
      memory: 2G     # Increase from 1G
```

**PHP-FPM Scaling**:
```ini
pm.max_children = 100    # Increase from 50
pm.start_servers = 20    # Increase from 10
```

### Database Scaling

**1. Read Replicas**:
```yaml
services:
  mysql-primary:
    image: mysql:8.0
    environment:
      MYSQL_REPLICATION_MODE: master
  
  mysql-replica:
    image: mysql:8.0
    environment:
      MYSQL_REPLICATION_MODE: slave
      MYSQL_MASTER_HOST: mysql-primary
```

**2. Connection Pooling**:
```php
// Use persistent connections
'mysql' => [
    'options' => [
        PDO::ATTR_PERSISTENT => true,
    ],
],
```

**3. Query Optimization**:
- Add indexes
- Use eager loading
- Cache query results

### Cache Scaling

**Redis Cluster**:
```yaml
services:
  redis-1:
    image: redis:alpine
    command: redis-server --cluster-enabled yes
  
  redis-2:
    image: redis:alpine
    command: redis-server --cluster-enabled yes
  
  redis-3:
    image: redis:alpine
    command: redis-server --cluster-enabled yes
```

**Redis Sentinel** (High Availability):
```yaml
services:
  redis-master:
    image: redis:alpine
  
  redis-sentinel:
    image: redis:alpine
    command: redis-sentinel /etc/redis/sentinel.conf
```

### CDN Integration

**Static Assets**:
- Serve static files from CDN
- Reduce load on application servers
- Improve global performance

**Configuration**:
```env
ASSET_URL=https://cdn.yourdomain.com
```

### Auto-Scaling

**Cloud Platforms**:
- AWS ECS/EKS with Auto Scaling Groups
- Azure Container Instances
- Google Cloud Run

**Metrics for Scaling**:
- CPU utilization > 70%
- Memory utilization > 80%
- Request queue length
- Response time > threshold

## Backup and Disaster Recovery

### Database Backups

**Manual Backup**:
```bash
# Export database
docker-compose exec mysql mysqldump -u root -p financial_tracker > backup_$(date +%Y%m%d).sql

# Import database
docker-compose exec -T mysql mysql -u root -p financial_tracker < backup_20231201.sql
```

**Automated Backups**:
```bash
# Cron job (Linux/macOS)
0 2 * * * cd /path/to/project && docker-compose exec -T mysql mysqldump -u root -proot financial_tracker > /backups/db_$(date +\%Y\%m\%d).sql
```

### Volume Backups

**Backup Volume**:
```bash
docker run --rm \
  -v fintrack_mysql_data:/data \
  -v $(pwd):/backup \
  alpine tar czf /backup/mysql_data_backup.tar.gz /data
```

**Restore Volume**:
```bash
docker run --rm \
  -v fintrack_mysql_data:/data \
  -v $(pwd):/backup \
  alpine tar xzf /backup/mysql_data_backup.tar.gz -C /
```

### Disaster Recovery Plan

**1. Regular Backups**:
- Daily database backups
- Weekly full system backups
- Store backups off-site

**2. Test Restores**:
- Monthly restore tests
- Document restore procedures
- Measure recovery time

**3. High Availability**:
- Database replication
- Load balancer redundancy
- Multi-region deployment

**4. Monitoring and Alerts**:
- Service health checks
- Automated failover
- Incident response plan

## Conclusion

This Docker architecture provides a robust, scalable, and maintainable environment for the Personal Financial Tracker application. Key benefits include:

- **Consistency**: Same environment across all stages
- **Isolation**: Services run independently
- **Scalability**: Easy to scale individual components
- **Portability**: Deploy anywhere Docker runs
- **Maintainability**: Clear structure and documentation

For questions or issues, refer to:
- [README.docker.md](README.docker.md) - Setup and usage guide
- [Docker Documentation](https://docs.docker.com/)
- [Laravel Documentation](https://laravel.com/docs)

---

**Document Version**: 1.0  
**Last Updated**: December 2024  
**Maintained By**: Development Team
