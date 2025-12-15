@echo off
REM ============================================================================
REM Docker Environment Setup Script for Windows
REM ============================================================================
REM This script sets up the Docker development environment for the
REM Personal Financial Tracker Laravel application.
REM ============================================================================

echo.
echo ============================================================================
echo   Personal Financial Tracker - Docker Environment Setup
echo ============================================================================
echo.

REM Check if Docker is installed
echo [1/9] Checking Docker installation...
docker --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not installed or not in PATH.
    echo Please install Docker Desktop from: https://www.docker.com/products/docker-desktop
    exit /b 1
)
echo [OK] Docker is installed
echo.

REM Check if Docker Compose is installed
echo [2/9] Checking Docker Compose installation...
docker-compose --version >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker Compose is not installed or not in PATH.
    echo Please install Docker Compose from: https://docs.docker.com/compose/install/
    exit /b 1
)
echo [OK] Docker Compose is installed
echo.

REM Check if Docker daemon is running
echo [3/9] Checking if Docker daemon is running...
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker daemon is not running.
    echo Please start Docker Desktop and try again.
    exit /b 1
)
echo [OK] Docker daemon is running
echo.

REM Copy environment file
echo [4/9] Setting up environment configuration...
if not exist .env.docker (
    if exist .env.docker.example (
        copy .env.docker.example .env.docker >nul
        echo [OK] Created .env.docker from .env.docker.example
    ) else (
        echo [WARNING] .env.docker.example not found, skipping...
    )
) else (
    echo [OK] .env.docker already exists
)
echo.

REM Build Docker containers
echo [5/9] Building Docker containers (this may take a few minutes)...
docker-compose build
if %errorlevel% neq 0 (
    echo [ERROR] Failed to build Docker containers
    exit /b 1
)
echo [OK] Docker containers built successfully
echo.

REM Start Docker containers
echo [6/9] Starting Docker containers...
docker-compose up -d
if %errorlevel% neq 0 (
    echo [ERROR] Failed to start Docker containers
    exit /b 1
)
echo [OK] Docker containers started successfully
echo.

REM Wait for services to be ready
echo [7/9] Waiting for services to be ready...
timeout /t 10 /nobreak >nul
echo [OK] Services should be ready
echo.

REM Install Composer dependencies
echo [8/9] Installing Composer dependencies...
docker-compose exec -T php composer install
if %errorlevel% neq 0 (
    echo [WARNING] Failed to install Composer dependencies
    echo You may need to run: docker-compose exec php composer install
) else (
    echo [OK] Composer dependencies installed
)
echo.

REM Install NPM dependencies
echo Installing NPM dependencies...
docker-compose exec -T node npm install
if %errorlevel% neq 0 (
    echo [WARNING] Failed to install NPM dependencies
    echo You may need to run: docker-compose exec node npm install
) else (
    echo [OK] NPM dependencies installed
)
echo.

REM Generate application key
echo Generating application key...
docker-compose exec -T php php artisan key:generate
if %errorlevel% neq 0 (
    echo [WARNING] Failed to generate application key
    echo You may need to run: docker-compose exec php php artisan key:generate
) else (
    echo [OK] Application key generated
)
echo.

REM Run database migrations
echo [9/9] Running database migrations...
docker-compose exec -T php php artisan migrate --force
if %errorlevel% neq 0 (
    echo [WARNING] Failed to run migrations
    echo You may need to run: docker-compose exec php php artisan migrate
) else (
    echo [OK] Database migrations completed
)
echo.

REM Run database seeders
echo Running database seeders...
docker-compose exec -T php php artisan db:seed --force
if %errorlevel% neq 0 (
    echo [WARNING] Failed to run seeders
    echo You may need to run: docker-compose exec php php artisan db:seed
) else (
    echo [OK] Database seeders completed
)
echo.

REM Display success message
echo ============================================================================
echo   Setup Complete!
echo ============================================================================
echo.
echo Your Docker development environment is ready!
echo.
echo Available services:
echo   - Application:  http://localhost
echo   - phpMyAdmin:   http://localhost:8080
echo   - Vite HMR:     http://localhost:5173
echo.
echo Useful commands:
echo   - View logs:           docker-compose logs -f
echo   - Stop containers:     docker-compose down
echo   - Restart containers:  docker-compose restart
echo   - Access PHP shell:    docker-compose exec php sh
echo   - Run artisan:         docker-compose exec php php artisan [command]
echo   - Run tests:           docker-compose exec php php artisan test
echo.
echo For more information, see README.docker.md
echo.
echo ============================================================================
