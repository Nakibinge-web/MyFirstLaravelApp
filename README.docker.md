# Docker Development Environment

This document provides comprehensive instructions for setting up and using the Docker development environment for the Personal Financial Tracker Laravel application.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Quick Start](#quick-start)
- [Available Services](#available-services)
- [Common Commands](#common-commands)
- [Troubleshooting](#troubleshooting)
- [FAQ](#faq)
- [Additional Resources](#additional-resources)

## Prerequisites

Before you begin, ensure you have the following installed on your system:

### Required Software

1. **Docker Desktop** (version 20.10 or higher)
   - Windows: [Download Docker Desktop for Windows](https://docs.docker.com/desktop/install/windows-install/)
   - macOS: [Download Docker Desktop for Mac](https://docs.docker.com/desktop/install/mac-install/)
   - Linux: [Install Docker Engine](https://docs.docker.com/engine/install/)

2. **Docker Compose** (version 2.0 or higher)
   - Included with Docker Desktop for Windows and macOS
   - Linux users: [Install Docker Compose](https://docs.docker.com/compose/install/)

### System Requirements

- **RAM:** Minimum 4GB, recommended 8GB or more
- **Disk Space:** At least 10GB free space
- **CPU:** 2 cores minimum, 4 cores recommended

### Verify Installation

Check that Docker and Docker Compose are properly installed:

```bash
docker --version
docker-compose --version
```

You should see version numbers for both commands.

## Quick Start

Follow these steps to get the application running with Docker:

### Step 1: Clone the Repository

```bash
git clone <repository-url>
cd personal-financial-tracker
```

### Step 2: Create Environment File

Copy the Docker environment example file:

```bash
# Windows (CMD)
copy .env.docker.example .env.docker

# Windows (PowerShell) / macOS / Linux
cp .env.docker.example .env.docker
```

Edit `.env.docker` if you need to customize any settings (database credentials, ports, etc.).

### Step 3: Build Docker Containers

Build all the Docker images:

```bash
docker-compose build
```

This process may take 5-10 minutes on the first run as it downloads base images and installs dependencies.

### Step 4: Start the Containers

Start all services in detached mode:

```bash
docker-compose up -d
```

### Step 5: Install Dependencies

Install PHP dependencies with Composer:

```bash
docker-compose exec php composer install
```

Install Node.js dependencies:

```bash
docker-compose exec node npm install
```

### Step 6: Generate Application Key

Generate the Laravel application key:

```bash
docker-compose exec php php artisan key:generate
```

### Step 7: Run Database Migrations

Create the database tables:

```bash
docker-compose exec php php artisan migrate
```

Optionally, seed the database with sample data:

```bash
docker-compose exec php php artisan db:seed
```

### Step 8: Access the Application

Open your browser and navigate to:

- **Application:** http://localhost
- **phpMyAdmin:** http://localhost:8080
- **Vite Dev Server:** http://localhost:5173

That's it! Your development environment is now ready.

### Quick Setup with Makefile (Optional)

If you have `make` installed, you can use the Makefile for a one-command setup:

```bash
make setup
```

This command will build containers, start services, install dependencies, generate the app key, and run migrations.

## Available Services

The Docker environment includes the following services:

### 1. PHP-FPM (php)

- **Container Name:** fintrack_php
- **Base Image:** php:8.2-fpm-alpine
- **Purpose:** Runs the Laravel application
- **Exposed Ports:** None (internal only, port 9000)
- **PHP Extensions:** PDO, MySQL, Mbstring, Exif, PCNTL, BCMath, GD, Zip
- **Includes:** Composer

### 2. Nginx (nginx)

- **Container Name:** fintrack_nginx
- **Base Image:** nginx:alpine
- **Purpose:** Web server for serving the application
- **Exposed Ports:** 80 → http://localhost
- **Configuration:** Optimized for Laravel routing

### 3. MySQL (mysql)

- **Container Name:** fintrack_mysql
- **Base Image:** mysql:8.0
- **Purpose:** Database server
- **Exposed Ports:** 3306 → localhost:3306
- **Default Database:** financial_tracker
- **Default User:** laravel
- **Default Password:** secret (configurable in .env.docker)
- **Root Password:** root (configurable in .env.docker)
- **Character Set:** utf8mb4
- **Collation:** utf8mb4_unicode_ci

### 4. Redis (redis)

- **Container Name:** fintrack_redis
- **Base Image:** redis:alpine
- **Purpose:** Cache and session storage
- **Exposed Ports:** 6379 → localhost:6379
- **Configuration:** Optimized for caching with LRU eviction policy
- **Max Memory:** 256MB

### 5. phpMyAdmin (phpmyadmin)

- **Container Name:** fintrack_phpmyadmin
- **Base Image:** phpmyadmin:latest
- **Purpose:** Web-based database management
- **Exposed Ports:** 8080 → http://localhost:8080
- **Default Login:** root / root (or credentials from .env.docker)

### 6. Node.js (node)

- **Container Name:** fintrack_node
- **Base Image:** node:20-alpine
- **Purpose:** Asset compilation and Vite dev server
- **Exposed Ports:** 5173 → http://localhost:5173
- **Features:** Hot Module Replacement (HMR) for frontend development

## Common Commands

### Using Docker Compose

#### Start Services

```bash
# Start all services in detached mode
docker-compose up -d

# Start specific service
docker-compose up -d nginx

# Start with logs visible
docker-compose up
```

#### Stop Services

```bash
# Stop all services
docker-compose down

# Stop and remove volumes (WARNING: deletes database data)
docker-compose down -v
```

#### View Logs

```bash
# View logs from all services
docker-compose logs

# Follow logs in real-time
docker-compose logs -f

# View logs from specific service
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f mysql
```

#### Check Service Status

```bash
# List all running containers
docker-compose ps

# View resource usage
docker stats
```

#### Restart Services

```bash
# Restart all services
docker-compose restart

# Restart specific service
docker-compose restart php
```

### Using the Makefile

If you have `make` installed, you can use these convenient commands:

```bash
make help          # Show all available commands
make build         # Build all containers
make up            # Start all containers
make down          # Stop all containers
make restart       # Restart all containers
make logs          # Show logs from all containers
make shell         # Access PHP container shell
make composer      # Run composer install
make npm           # Run npm install
make test          # Run PHPUnit tests
make migrate       # Run database migrations
make seed          # Run database seeders
make fresh         # Fresh database with seeders
make setup         # Complete initial setup
```

### Laravel Artisan Commands

```bash
# Run any artisan command
docker-compose exec php php artisan <command>

# Examples:
docker-compose exec php php artisan migrate
docker-compose exec php php artisan db:seed
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan config:cache
docker-compose exec php php artisan route:list
docker-compose exec php php artisan tinker
```

With Makefile:

```bash
make artisan cmd="migrate"
make artisan cmd="cache:clear"
```

### Composer Commands

```bash
# Install dependencies
docker-compose exec php composer install

# Update dependencies
docker-compose exec php composer update

# Require new package
docker-compose exec php composer require vendor/package

# Dump autoload
docker-compose exec php composer dump-autoload
```

### NPM Commands

```bash
# Install dependencies
docker-compose exec node npm install

# Run development server
docker-compose exec node npm run dev

# Build for production
docker-compose exec node npm run build

# Install new package
docker-compose exec node npm install package-name
```

### Database Commands

```bash
# Access MySQL CLI
docker-compose exec mysql mysql -u root -p

# Export database
docker-compose exec mysql mysqldump -u root -p financial_tracker > backup.sql

# Import database
docker-compose exec -T mysql mysql -u root -p financial_tracker < backup.sql

# Access Redis CLI
docker-compose exec redis redis-cli
```

### Container Shell Access

```bash
# Access PHP container
docker-compose exec php sh

# Access Node container
docker-compose exec node sh

# Access MySQL container
docker-compose exec mysql bash

# Access Nginx container
docker-compose exec nginx sh
```

## Troubleshooting

### Common Issues and Solutions

#### Issue: Port Already in Use

**Symptom:** Error message like "Bind for 0.0.0.0:80 failed: port is already allocated"

**Solution:**

1. Check what's using the port:
   ```bash
   # Windows
   netstat -ano | findstr :80
   
   # macOS/Linux
   lsof -i :80
   ```

2. Stop the conflicting service or change the port in `docker-compose.yml`:
   ```yaml
   nginx:
     ports:
       - "8000:80"  # Use port 8000 instead
   ```

#### Issue: Containers Won't Start

**Symptom:** Containers exit immediately or show "Exited (1)" status

**Solution:**

1. Check the logs:
   ```bash
   docker-compose logs <service-name>
   ```

2. Common causes:
   - Missing or incorrect environment variables in `.env.docker`
   - Insufficient disk space
   - Corrupted Docker images (try rebuilding: `docker-compose build --no-cache`)

#### Issue: Permission Denied Errors

**Symptom:** Laravel shows "Permission denied" errors for storage or cache directories

**Solution:**

```bash
# Fix permissions inside PHP container
docker-compose exec php chown -R www-data:www-data storage bootstrap/cache
docker-compose exec php chmod -R 775 storage bootstrap/cache
```

#### Issue: Database Connection Refused

**Symptom:** "SQLSTATE[HY000] [2002] Connection refused"

**Solution:**

1. Ensure MySQL container is running:
   ```bash
   docker-compose ps mysql
   ```

2. Check that `.env.docker` has correct settings:
   ```env
   DB_HOST=mysql  # Must be "mysql", not "localhost"
   DB_PORT=3306
   ```

3. Wait for MySQL to fully start (check health status):
   ```bash
   docker-compose ps
   ```

#### Issue: Composer/NPM Install Fails

**Symptom:** Timeout or network errors during dependency installation

**Solution:**

1. Increase Docker memory allocation in Docker Desktop settings (minimum 4GB)

2. Try installing with increased timeout:
   ```bash
   docker-compose exec php composer install --no-interaction --timeout=600
   ```

3. Clear caches and retry:
   ```bash
   docker-compose exec php composer clear-cache
   docker-compose exec node npm cache clean --force
   ```

#### Issue: Vite HMR Not Working

**Symptom:** Changes to frontend files don't trigger hot reload

**Solution:**

1. Ensure `vite.config.js` has correct configuration:
   ```js
   server: {
     host: '0.0.0.0',
     port: 5173,
     hmr: {
       host: 'localhost'
     }
   }
   ```

2. Restart the Node container:
   ```bash
   docker-compose restart node
   ```

#### Issue: MySQL Container Keeps Restarting

**Symptom:** MySQL container shows "Restarting" status repeatedly

**Solution:**

1. Check MySQL logs:
   ```bash
   docker-compose logs mysql
   ```

2. Common causes:
   - Corrupted data volume (remove and recreate: `docker-compose down -v`)
   - Insufficient memory
   - Invalid configuration in `docker/mysql/my.cnf`

#### Issue: Slow Performance on Windows

**Symptom:** Application runs very slowly, especially file operations

**Solution:**

1. Enable WSL 2 backend in Docker Desktop settings
2. Move project to WSL 2 filesystem (not Windows filesystem)
3. Use named volumes instead of bind mounts for vendor and node_modules

#### Issue: "No such file or directory" for Composer/Artisan

**Symptom:** Commands fail with file not found errors

**Solution:**

Ensure you're running commands inside the container:

```bash
# Wrong (runs on host)
php artisan migrate

# Correct (runs in container)
docker-compose exec php php artisan migrate
```

### Resetting the Environment

If you encounter persistent issues, try resetting the entire environment:

```bash
# Stop all containers
docker-compose down

# Remove all volumes (WARNING: deletes all data)
docker-compose down -v

# Remove all images
docker-compose down --rmi all

# Rebuild from scratch
docker-compose build --no-cache

# Start fresh
docker-compose up -d

# Reinstall dependencies
docker-compose exec php composer install
docker-compose exec node npm install

# Regenerate key and migrate
docker-compose exec php php artisan key:generate
docker-compose exec php php artisan migrate --seed
```

## FAQ

### Q: Do I need to install PHP, Composer, Node.js, or MySQL on my machine?

**A:** No! That's the beauty of Docker. All dependencies run inside containers. You only need Docker and Docker Compose installed.

### Q: Can I use my local database tools (like MySQL Workbench)?

**A:** Yes! MySQL is exposed on port 3306. Connect using:
- Host: localhost (or 127.0.0.1)
- Port: 3306
- Username: laravel (or root)
- Password: secret (or root)

### Q: How do I run PHPUnit tests?

**A:** Use this command:
```bash
docker-compose exec php php artisan test
```

Or with Makefile:
```bash
make test
```

### Q: Can I use this setup for production?

**A:** The current setup is optimized for development. For production, use `docker-compose.prod.yml` (if available) or create a production-specific configuration with:
- Multi-stage builds
- No development tools (phpMyAdmin, Vite dev server)
- Optimized PHP and Nginx settings
- Proper secrets management
- Health checks and monitoring

### Q: How do I update PHP or Node.js versions?

**A:** Edit the Dockerfile in `docker/php/Dockerfile` or `docker/node/Dockerfile`, change the base image version, then rebuild:
```bash
docker-compose build --no-cache php
docker-compose build --no-cache node
```

### Q: Where is my data stored?

**A:** Data is stored in Docker volumes:
- MySQL data: `mysql_data` volume
- Redis data: `redis_data` volume
- Application code: Bind mounted from your project directory

To see volumes:
```bash
docker volume ls
```

### Q: How do I backup my database?

**A:** Export the database:
```bash
docker-compose exec mysql mysqldump -u root -p financial_tracker > backup_$(date +%Y%m%d).sql
```

Restore from backup:
```bash
docker-compose exec -T mysql mysql -u root -p financial_tracker < backup_20231201.sql
```

### Q: Can I run multiple Laravel projects with Docker simultaneously?

**A:** Yes, but you need to:
1. Use different project directories
2. Change port mappings in each project's `docker-compose.yml` to avoid conflicts
3. Use different container names

### Q: How do I stop Docker from using so much disk space?

**A:** Clean up unused Docker resources:
```bash
# Remove unused containers, networks, and images
docker system prune

# Remove unused volumes (WARNING: may delete data)
docker volume prune

# See disk usage
docker system df
```

### Q: Why is my application showing a blank page?

**A:** Common causes:
1. Check PHP logs: `docker-compose logs php`
2. Ensure `.env.docker` is properly configured
3. Run `docker-compose exec php php artisan config:clear`
4. Check file permissions on storage and cache directories

### Q: MySQL Container Shows MYSQL_USER="root" Error

**A:** The issue occurs when MYSQL_USER is set to "root". The docker-compose.yml should only use:
- `MYSQL_ROOT_PASSWORD` for the root password
- `MYSQL_DATABASE` for the database name

Do not set `MYSQL_USER` or `MYSQL_PASSWORD` when using root access. This has been fixed in the current docker-compose.yml configuration.

### Q: How do I run Laravel commands inside containers?

**A:** Always prefix Laravel commands with `docker-compose exec php`:

```bash
# Wrong (runs on host)
php artisan migrate

# Correct (runs in container)
docker-compose exec php php artisan migrate
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan config:cache
```

### Q: Can I use different database credentials?

**A:** Yes! Edit `.env.docker` and update the database credentials:

```env
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_ROOT_PASSWORD=your_root_password
```

Then restart the containers:
```bash
docker-compose down -v  # Remove old database
docker-compose up -d    # Start with new credentials
```

### Q: How do I check if all services are healthy?

**A:** Use the following command to see health status:

```bash
docker-compose ps

# Look for "healthy" status in the output
# If a service shows "unhealthy", check its logs:
docker-compose logs <service-name>
```

### Q: What if I need to change PHP or Nginx configuration?

**A:** Edit the configuration files in the `docker/` directory:

- PHP settings: `docker/php/php.ini`
- PHP-FPM pool: `docker/php/www.conf`
- Nginx server: `docker/nginx/default.conf`
- Nginx global: `docker/nginx/nginx.conf`

After making changes, restart the affected service:
```bash
docker-compose restart php
docker-compose restart nginx
```

### Q: Containers Not Accessible on Windows

**A:** If you cannot access services on localhost even though containers are running:

1. Check if containers are actually running: `docker-compose ps`
2. Verify port bindings: `docker-compose port nginx 80`
3. Check Windows Firewall settings - ensure Docker is allowed
4. Try accessing via 127.0.0.1 instead of localhost
5. Restart Docker Desktop if issues persist

### Q: Volume Permission Issues on Windows

**A:** If you get permission denied errors when containers try to access volumes:

1. Ensure Docker Desktop has access to the drive:
   - Open Docker Desktop Settings
   - Go to Resources → File Sharing
   - Add your project drive if not listed
2. Check that volumes are properly mounted in docker-compose.yml
3. Try resetting Docker Desktop to factory defaults if issues persist

### Q: How do I access Laravel Telescope or Horizon?

**A:** If you have these packages installed, access them at:
- Telescope: http://localhost/telescope
- Horizon: http://localhost/horizon

Make sure they're enabled in your `.env.docker` file.

## Additional Resources

### Project Documentation

- [DOCKER_ARCHITECTURE.md](DOCKER_ARCHITECTURE.md) - Detailed architecture diagrams and technical documentation
- [Makefile](Makefile) - Available make commands for common operations
- [.env.docker.example](.env.docker.example) - Environment variable reference

### Official Documentation

- [Docker Documentation](https://docs.docker.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Laravel Documentation](https://laravel.com/docs)
- [Laravel Sail](https://laravel.com/docs/sail) - Laravel's official Docker environment

### Docker Learning Resources

- [Docker Getting Started Tutorial](https://docs.docker.com/get-started/)
- [Docker Compose Getting Started](https://docs.docker.com/compose/gettingstarted/)
- [Best Practices for Writing Dockerfiles](https://docs.docker.com/develop/develop-images/dockerfile_best-practices/)

### Laravel Resources

- [Laravel Installation Guide](https://laravel.com/docs/installation)
- [Laravel Configuration](https://laravel.com/docs/configuration)
- [Laravel Database](https://laravel.com/docs/database)

### Community Support

- [Docker Community Forums](https://forums.docker.com/)
- [Laravel Forums](https://laracasts.com/discuss)
- [Stack Overflow - Docker](https://stackoverflow.com/questions/tagged/docker)
- [Stack Overflow - Laravel](https://stackoverflow.com/questions/tagged/laravel)

### Video Tutorials

- [Docker Tutorial for Beginners](https://www.youtube.com/watch?v=fqMOX6JJhGo)
- [Laravel Docker Setup](https://laracasts.com/series/guest-spotlight/episodes/1)

---

## Need Help?

If you encounter issues not covered in this documentation:

1. Check the [Troubleshooting](#troubleshooting) section
2. Review container logs: `docker-compose logs`
3. Search for similar issues on Stack Overflow or GitHub
4. Contact the development team

Happy coding! 🚀
