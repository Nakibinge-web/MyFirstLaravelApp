# Docker Environment Testing Guide

This guide provides instructions for testing and validating the Docker environment for the Personal Financial Tracker application.

## Prerequisites

- Docker and Docker Compose installed
- PowerShell (for Windows)
- All Docker configuration files in place

## Quick Start

To run all tests at once:

```powershell
.\run-all-docker-tests.ps1
```

## Individual Test Scripts

### 13.1 Test Container Builds and Startup

**Script:** `test-docker-startup.ps1`

**What it tests:**
- Builds all containers using docker-compose build
- Starts all containers using docker-compose up -d
- Verifies all containers are running with docker-compose ps
- Checks container logs for errors

**Run:**
```powershell
.\test-docker-startup.ps1
```

**Expected Results:**
- All containers should be in "running" state
- No error messages in logs
- Health checks should pass for MySQL and Redis

---

### 13.2 Test Service Connectivity

**Script:** `test-service-connectivity.ps1`

**What it tests:**
- PHP to MySQL connection by running migrations
- PHP to Redis connection using artisan tinker
- Nginx to PHP-FPM by accessing application in browser
- phpMyAdmin access on port 8080
- Vite dev server on port 5173

**Run:**
```powershell
.\test-service-connectivity.ps1
```

**Manual verification required:**
- Open http://localhost in browser (Nginx → PHP-FPM)
- Open http://localhost:8080 in browser (phpMyAdmin)
- Open http://localhost:5173 in browser (Vite dev server)

**Expected Results:**
- Migration status displays correctly
- Redis ping returns successful response
- All web interfaces are accessible

---

### 13.3 Test Application Functionality

**Script:** `test-application-functionality.ps1`

**What it tests:**
- Access application at http://localhost
- Run database migrations successfully
- Run database seeders successfully
- Test cache operations (cache:clear, config:cache)
- Test asset compilation (npm run build)
- Run PHPUnit test suite

**Run:**
```powershell
.\test-application-functionality.ps1
```

**Expected Results:**
- Migrations run without errors
- Seeders populate database successfully
- Cache commands execute successfully
- Assets compile without errors
- All tests pass

---

### 13.4 Test Volume Persistence

**Script:** `test-volume-persistence.ps1`

**What it tests:**
- Create test data in database
- Stop and restart containers
- Verify data persists after restart
- Test Redis data persistence

**Run:**
```powershell
.\test-volume-persistence.ps1
```

**Expected Results:**
- Test data in MySQL persists after container restart
- Test data in Redis persists after container restart
- No data loss during container lifecycle

---

### 13.5 Test Development Workflow

**Script:** `test-development-workflow.ps1`

**What it tests:**
- PHP file changes reflect immediately
- Frontend file changes and HMR works
- Makefile commands (if available)
- Helper scripts (setup.sh, cleanup.sh)
- Common Docker commands

**Run:**
```powershell
.\test-development-workflow.ps1
```

**Manual verification required:**
- Make changes to PHP files and verify they reflect immediately
- Make changes to frontend files and verify HMR works

**Expected Results:**
- File changes are immediately visible
- HMR updates browser without full reload
- Helper scripts are accessible
- Docker exec commands work correctly

---

## Manual Testing Checklist

After running the automated scripts, verify the following manually:

### Browser Access
- [ ] Application loads at http://localhost
- [ ] phpMyAdmin loads at http://localhost:8080
- [ ] Vite dev server loads at http://localhost:5173

### File Changes
- [ ] Edit a PHP file (e.g., routes/web.php) and verify changes appear on refresh
- [ ] Edit a frontend file (e.g., resources/js/app.js) and verify HMR updates

### Database Operations
- [ ] Login to phpMyAdmin with root/root
- [ ] Verify database tables exist
- [ ] Verify seeded data is present

### Container Management
- [ ] Run `docker-compose ps` to see all containers
- [ ] Run `docker-compose logs -f` to see live logs
- [ ] Run `docker-compose exec php sh` to access PHP container shell

---

## Troubleshooting

### MySQL Container Fails to Start

**Issue:** MySQL container shows error about MYSQL_USER="root"

**Solution:** The docker-compose.yml has been updated to not set MYSQL_USER when using root. Ensure you're using the latest version.

### Containers Not Accessible

**Issue:** Cannot access services on localhost

**Solution:** 
1. Check if containers are running: `docker-compose ps`
2. Check port bindings: `docker-compose port nginx 80`
3. Check firewall settings

### Volume Permission Issues

**Issue:** Permission denied errors in containers

**Solution:**
1. On Windows, ensure Docker Desktop has access to the drive
2. Check volume mounts in docker-compose.yml
3. Verify file permissions

### Database Connection Errors

**Issue:** PHP cannot connect to MySQL

**Solution:**
1. Verify DB_HOST=mysql in .env (not 127.0.0.1)
2. Verify DB_PASSWORD matches MYSQL_ROOT_PASSWORD
3. Wait for MySQL health check to pass before connecting

---

## Configuration Files Updated

The following files were updated to fix Docker environment issues:

1. **docker-compose.yml**
   - Removed MYSQL_USER and MYSQL_PASSWORD environment variables to allow root user
   - Kept MYSQL_ROOT_PASSWORD and MYSQL_DATABASE

2. **.env**
   - Changed DB_HOST from 127.0.0.1 to mysql
   - Changed REDIS_HOST from 127.0.0.1 to redis
   - Added DB_ROOT_PASSWORD=root
   - Set DB_PASSWORD=root

---

## Requirements Coverage

This testing suite covers the following requirements:

- **1.1, 1.4, 1.5:** Container builds and startup
- **1.3, 3.3, 4.5, 5.2, 6.2, 7.5:** Service connectivity
- **2.1, 4.1, 5.1, 7.2, 7.3:** Application functionality
- **9.2, 9.3, 9.6:** Volume persistence
- **10.1, 10.2, 10.3, 10.4, 10.5:** Development workflow

---

## Next Steps

After all tests pass:

1. Commit the Docker configuration changes
2. Update project documentation
3. Share the testing guide with the team
4. Set up CI/CD pipeline for automated testing
